<?php

class DataTable {

	private $sql = "";
	private $db_result = [];
	private $total_rows = 0;
	private $columns = [];

	public function __construct($result) {
		/* Check if its a PDO Statement object */
		if(get_class($result) !== "PDOStatement") {
			throw new Error("Result input is not a PDOStatement object");
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

		$request_columns = $params["columns"];
		$request_column_order = $params["order"][0];
		$request_column_order_number = $params["order"][0]["column"];	# Column number
		$order_column_order_dir = $params["order"][0]["dir"];	# ASC or DESC
		
		/* Is empty */
		if(!isset($request_columns[0])) {
			return $response;
		}
		/* Order description */
		$order_column_name = $request_columns[$request_column_order_number]["data"];

		/* Separating the array based on the order key only */
		$order_column_array = [];
		foreach($this->db_result as $key => $value) {
			/* If it's the order column */
			$order_column_array[] = $value[$order_column_name];
		}

		if(strtolower($order_column_order_dir) == "asc") {
			sort($order_column_array);
		} else {
			rsort($order_column_array);
		}

		/* Mergin the separated column array */
		foreach($this->db_result as $key => $value) {
			$this->db_result[$key][$order_column_name] = $order_column_array[$key];
		}
		#print_r($this->db_result);
		#die();
		$response["data"] = $this->db_result;
		$response["draw"] = $params["draw"];
		return $response;
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
}