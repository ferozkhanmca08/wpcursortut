<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Employee_Manager {
	private static $instance = null;
	private $table_name;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'employees';
	}

	public static function activate() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		global $wpdb;
		$table_name = $wpdb->prefix . 'employees';
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE {$table_name} (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(191) NOT NULL,
			dept VARCHAR(191) NOT NULL,
			salary DECIMAL(15,2) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) {$charset_collate};";
		dbDelta( $sql );
	}

	public static function deactivate() {
		// No-op: keep data by default.
	}

	public function get_table() {
		return $this->table_name;
	}

	public function validate_employee_input( $data, $is_update = false ) {
		$errors = [];
		$name = isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : '';
		$dept = isset( $data['dept'] ) ? sanitize_text_field( $data['dept'] ) : '';
		$salary_raw = isset( $data['salary'] ) ? $data['salary'] : '';

		if ( ! $is_update || $name !== '' ) {
			if ( $name === '' ) {
				$errors[] = 'Name is required.';
			}
		}
		if ( ! $is_update || $dept !== '' ) {
			if ( $dept === '' ) {
				$errors[] = 'Department is required.';
			}
		}
		if ( ! $is_update || $salary_raw !== '' ) {
			if ( $salary_raw === '' || ! is_numeric( $salary_raw ) ) {
				$errors[] = 'Salary must be numeric.';
			}
		}

		return [
			'errors' => $errors,
			'clean'  => [
				'name'   => $name,
				'dept'   => $dept,
				'salary' => $salary_raw === '' ? null : floatval( $salary_raw ),
			],
		];
	}

	public function create_employee( $name, $dept, $salary ) {
		global $wpdb;
		$inserted = $wpdb->insert(
			$this->get_table(),
			[
				'name'   => $name,
				'dept'   => $dept,
				'salary' => $salary,
			],
			[ '%s', '%s', '%f' ]
		);
		if ( false === $inserted ) {
			return new WP_Error( 'db_insert_failed', 'Failed to create employee.' );
		}
		return (int) $wpdb->insert_id;
	}

	public function update_employee( $id, $fields ) {
		global $wpdb;
		$set = [];
		$formats = [];
		if ( array_key_exists( 'name', $fields ) ) {
			$set['name'] = $fields['name'];
			$formats[] = '%s';
		}
		if ( array_key_exists( 'dept', $fields ) ) {
			$set['dept'] = $fields['dept'];
			$formats[] = '%s';
		}
		if ( array_key_exists( 'salary', $fields ) ) {
			$set['salary'] = $fields['salary'];
			$formats[] = '%f';
		}
		if ( empty( $set ) ) {
			return 0;
		}
		$updated = $wpdb->update(
			$this->get_table(),
			$set,
			[ 'id' => $id ],
			$formats,
			[ '%d' ]
		);
		if ( false === $updated ) {
			return new WP_Error( 'db_update_failed', 'Failed to update employee.' );
		}
		return (int) $updated;
	}

	public function delete_employee( $id ) {
		global $wpdb;
		$deleted = $wpdb->delete( $this->get_table(), [ 'id' => $id ], [ '%d' ] );
		if ( false === $deleted ) {
			return new WP_Error( 'db_delete_failed', 'Failed to delete employee.' );
		}
		return (int) $deleted;
	}

	public function get_employee( $id ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT id, name, dept, salary, created_at, updated_at FROM {$this->get_table()} WHERE id = %d", $id ),
			ARRAY_A
		);
		return $row;
	}

	public function list_employees( $args = [] ) {
		global $wpdb;
		$defaults = [
			'offset' => 0,
			'limit'  => 50,
			'search' => '',
			'dept'   => '',
		];
		$args = wp_parse_args( $args, $defaults );

		$where = 'WHERE 1=1';
		$params = [];
		if ( $args['search'] !== '' ) {
			$where .= " AND (name LIKE %s OR dept LIKE %s)";
			$like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $like;
			$params[] = $like;
		}
		if ( $args['dept'] !== '' ) {
			$where .= " AND dept = %s";
			$params[] = $args['dept'];
		}
		$limit_clause = " LIMIT %d OFFSET %d";
		$params[] = (int) $args['limit'];
		$params[] = (int) $args['offset'];

		$sql = "SELECT id, name, dept, salary, created_at, updated_at FROM {$this->get_table()} {$where} ORDER BY id DESC {$limit_clause}";
		$prepared = $wpdb->prepare( $sql, $params );
		$rows = $wpdb->get_results( $prepared, ARRAY_A );
		return $rows;
	}
}


