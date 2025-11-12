<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Employee_Manager_REST {
	private static $instance = null;
	private $namespace = 'employee/v1';

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/employees',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'list_employees' ],
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
					'args'                => [
						'offset' => [ 'type' => 'integer', 'default' => 0 ],
						'limit'  => [ 'type' => 'integer', 'default' => 50 ],
						'search' => [ 'type' => 'string', 'default' => '' ],
						'dept'   => [ 'type' => 'string', 'default' => '' ],
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_employee' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => $this->get_schema_args( false ),
				],
			]
		);

		register_rest_route(
			$this->namespace,
			'/employees/(?P<id>\\d+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_employee' ],
					'permission_callback' => function () {
						return current_user_can( 'read' );
					},
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_employee' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
					'args'                => $this->get_schema_args( true ),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_employee' ],
					'permission_callback' => function () {
						return current_user_can( 'manage_options' );
					},
				],
			]
		);
	}

	private function get_schema_args( $is_update ) {
		$required = ! $is_update;
		return [
			'name'   => [
				'type'     => 'string',
				'required' => $required,
			],
			'dept'   => [
				'type'     => 'string',
				'required' => $required,
			],
			'salary' => [
				'type'     => 'number',
				'required' => $required,
			],
		];
	}

	public function list_employees( WP_REST_Request $request ) {
		$mgr = Employee_Manager::instance();
		$items = $mgr->list_employees(
			[
				'offset' => (int) $request->get_param( 'offset' ),
				'limit'  => (int) $request->get_param( 'limit' ),
				'search' => (string) $request->get_param( 'search' ),
				'dept'   => (string) $request->get_param( 'dept' ),
			]
		);
		return rest_ensure_response( $items );
	}

	public function create_employee( WP_REST_Request $request ) {
		$mgr = Employee_Manager::instance();
		$validated = $mgr->validate_employee_input(
			[
				'name'   => $request->get_param( 'name' ),
				'dept'   => $request->get_param( 'dept' ),
				'salary' => $request->get_param( 'salary' ),
			],
			false
		);
		if ( ! empty( $validated['errors'] ) ) {
			return new WP_Error( 'rest_invalid_param', implode( ' ', $validated['errors'] ), [ 'status' => 400 ] );
		}
		$id = $mgr->create_employee( $validated['clean']['name'], $validated['clean']['dept'], $validated['clean']['salary'] );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		$item = $mgr->get_employee( $id );
		return rest_ensure_response( $item );
	}

	public function get_employee( WP_REST_Request $request ) {
		$mgr = Employee_Manager::instance();
		$id = (int) $request['id'];
		$item = $mgr->get_employee( $id );
		if ( ! $item ) {
			return new WP_Error( 'rest_not_found', 'Employee not found', [ 'status' => 404 ] );
		}
		return rest_ensure_response( $item );
	}

	public function update_employee( WP_REST_Request $request ) {
		$mgr = Employee_Manager::instance();
		$id = (int) $request['id'];
		if ( ! $mgr->get_employee( $id ) ) {
			return new WP_Error( 'rest_not_found', 'Employee not found', [ 'status' => 404 ] );
		}
		$validated = $mgr->validate_employee_input(
			[
				'name'   => $request->get_param( 'name' ),
				'dept'   => $request->get_param( 'dept' ),
				'salary' => $request->get_param( 'salary' ),
			],
			true
		);
		if ( ! empty( $validated['errors'] ) ) {
			return new WP_Error( 'rest_invalid_param', implode( ' ', $validated['errors'] ), [ 'status' => 400 ] );
		}
		$fields = array_filter(
			$validated['clean'],
			function ( $v ) {
				return $v !== null && $v !== '';
			}
		);
		$updated = $mgr->update_employee( $id, $fields );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}
		$item = $mgr->get_employee( $id );
		return rest_ensure_response( $item );
	}

	public function delete_employee( WP_REST_Request $request ) {
		$mgr = Employee_Manager::instance();
		$id = (int) $request['id'];
		if ( ! $mgr->get_employee( $id ) ) {
			return new WP_Error( 'rest_not_found', 'Employee not found', [ 'status' => 404 ] );
		}
		$deleted = $mgr->delete_employee( $id );
		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}
		return rest_ensure_response( [ 'deleted' => (bool) $deleted ] );
	}
}


