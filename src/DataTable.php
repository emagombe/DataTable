<?php

namespace emagombe;

class DataTable {

	private $sql = "";
	private $db_result = [];
	private $total_rows = 0;
	private $columns = [];

	public function __construct($result) {
		/* Check if is array */
		if(is_array($result)) {

			/* Getting columns */
			$columns = [];
			if(isset($result[0])) {
				foreach($result[0] as $key => $value) {
					$columns[] = $key;
				}
			}

			/* Updating total number of rows */
			$this->total_rows = count($result);
			$this->columns = $columns;
			$this->db_result = $result;
		} else {
			/* Check if its a PDO Statement object */
			if(get_class($result) !== "PDOStatement") {
				throw new Error("Result input is not a PDOStatement object or Array");
			}

			if(!$this->isSelect($result->queryString)) {
				throw new Error("Only Select query allowed");
			}
			$this->sql = $result->queryString;

			/* Preventing SQL errors */
			if(!$result->execute()) {
				throw new Error("Failed executing query [" . $result->errorInfo()[0]."] ".$result->errorInfo()[2]);
			}

			$db_result = [];
			/* Getting database response */
			$row_id_counter = 0;
			foreach($result as $key => $value) {
				$filtered_data = [];
				/* Removing numeric indexes from result */
				foreach((array) $value as $_key => $_value) {

					if(is_int($_key)) { continue; }
					$filtered_data[$_key] = $_value;
				}
				/* Adding extra DataTable Keys */
				$filtered_data["DT_RowId"] = "row_$row_id_counter";
				$db_result[$key] = $filtered_data;

				$row_id_counter ++;
			}

			/* Getting columns */
			$columns = [];
			if(isset($db_result[0])) {
				foreach($db_result[0] as $key => $value) {
					$columns[] = $key;
				}
			}

			/* Updating total number of rows */
			$this->total_rows = $result->rowCount();
			$this->columns = $columns;
			$this->db_result = $db_result;
		}
	}

	/* Create DataTable object with static method */
	public static function create($result) {

		return new DataTable($result);
	}

	public function build() {

		/* Building result */
		$response = [];

		$params = $this->getParams();

		$response["draw"] = 1;
		$response["recordsTotal"] = $this->total_rows;
		$response["recordsFiltered"] = $this->total_rows;
		$response["data"] = $this->db_result;

		/* Return if no parameter sent */
		if(!$params) {
			return $response;
		}

		/* Is empty */
		if(!isset($params["columns"][0])) {
			return $response;
		}

		/* Sorting result */
		$this->db_result = $this->sort($this->db_result);

		/* Filtering result */
		if($params["search"]["value"] != "") {
			$this->db_result = $this->search($this->db_result);
		}

		/* Updating filtered records number */
		$response["recordsFiltered"] = count($this->db_result);

		/* Limit */
		$start = intval($params["start"]);
		$length = intval($params["length"]);
		$this->db_result = array_slice($this->db_result, $start, $length);

		$response["data"] = $this->db_result;
		$response["draw"] = $params["draw"];
		return json_encode($response);
	}

	/* Add column to table */
	public function addColumn($column_name, $callback) {
		$result = [];
		foreach($this->db_result as $index => $row) {
			$row[$column_name] = $callback($row);
			$result[] = $row;
		}
		$this->db_result = $result;
		return $this;
	}

	/* Check if it's a select query */
	private function isSelect($string) {
		if(strtoupper(substr($string, 0, strlen("SELECT"))) === "SELECT") {
			return true;
		}
		return false;
	}

	/* Getting datatable parameters */
	private function getParams() {
		if(isset($_GET["draw"])) {
			return $_GET;
		}
		if(isset($_POST["draw"])) {
			return $_POST;
		}
		return false;
	}

	private function sort($result) {
		$params = $this->getParams();

		$request_columns = $params["columns"];
		$request_column_order = $params["order"][0];
		$request_column_order_number = $params["order"][0]["column"];	# Column number
		$order_column_order_dir = $params["order"][0]["dir"];	# ASC or DESC

		/* Order description */
		$order_column_name = $request_columns[$request_column_order_number]["data"];
		$order_column_orderable = $request_columns[$request_column_order_number]["orderable"];

		/* Not orderable */
		if(!$order_column_orderable == "true") {
			return $result;
		}
		/* Separating the array based on the order key only */
		$order_column_array = [];
		foreach($result as $key => $value) {
			/* If it's the order column */
			$order_column_array[] = $value[$order_column_name];
		}

		/* Ordering column */
		if(strtolower($order_column_order_dir) == "asc") {
			asort($order_column_array);
		} else {
			arsort($order_column_array);
		}

		/* Ordering the whole result array */
		$sorted_result = [];
		foreach($order_column_array as $key => $value) {
			$sorted_result[] = $result[$key];
		}
		return $sorted_result;
	}

	private function search($result) {
		$params = $this->getParams();

		$request_columns = $params["columns"];
		$request_search_query = $params["search"]["value"];

		$filtered_result = [];
		foreach($result as $index => $row) {
			/* Filter columns */
			foreach($row as $key => $value) {
				$break = false;
				/* Check if column is in the table and is searchable */
				foreach($params["columns"] as $column) {
					if($column["data"] == $key && $column["searchable"] == "true") {
						/* Check if contains search key */
						if(strpos($value, $request_search_query) !== false) {
							$filtered_result[] = $row;
							$break = true;
							break;
						}
					}
				}
				/* Breaking if key word found */
				if($break) {
					break;
				}
			}
		}
		return $filtered_result;
	}
}