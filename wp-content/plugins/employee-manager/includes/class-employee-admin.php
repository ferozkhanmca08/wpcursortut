<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Employee_Manager_Admin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_post_emp_create', [ $this, 'handle_create' ] );
		add_action( 'admin_post_emp_update', [ $this, 'handle_update' ] );
		add_action( 'admin_post_emp_delete', [ $this, 'handle_delete' ] );
	}

	public function register_menu() {
		add_menu_page(
			'Employees',
			'Employees',
			'manage_options',
			'employee-manager',
			[ $this, 'render_list_page' ],
			'dashicons-groups',
			26
		);
		add_submenu_page(
			'employee-manager',
			'Add New Employee',
			'Add New',
			'manage_options',
			'employee-manager-add',
			[ $this, 'render_add_page' ]
		);
	}

	private function require_cap() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions.' );
		}
	}

	public function render_list_page() {
		$this->require_cap();
		$mgr = Employee_Manager::instance();
		$employees = $mgr->list_employees( [] );
		$action_url = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1>Employees <a href="<?php echo esc_url( admin_url( 'admin.php?page=employee-manager-add' ) ); ?>" class="page-title-action">Add New</a></h1>
			<table class="widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th>
						<th>Name</th>
						<th>Department</th>
						<th>Salary</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php if ( empty( $employees ) ) : ?>
					<tr><td colspan="5">No employees found.</td></tr>
				<?php else : ?>
					<?php foreach ( $employees as $emp ) : ?>
						<tr>
							<td><?php echo (int) $emp['id']; ?></td>
							<td><?php echo esc_html( $emp['name'] ); ?></td>
							<td><?php echo esc_html( $emp['dept'] ); ?></td>
							<td><?php echo esc_html( number_format( (float) $emp['salary'], 2 ) ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=employee-manager-add&edit=' . (int) $emp['id'] ), 'emp_edit_' . (int) $emp['id'] ) ); ?>">Edit</a>
								|
								<form method="post" action="<?php echo esc_url( $action_url ); ?>" style="display:inline;">
									<input type="hidden" name="action" value="emp_delete" />
									<input type="hidden" name="id" value="<?php echo (int) $emp['id']; ?>" />
									<?php wp_nonce_field( 'emp_delete_' . (int) $emp['id'] ); ?>
									<button type="submit" class="link-delete" onclick="return confirm('Delete this employee?');">Delete</button>
								</form>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	public function render_add_page() {
		$this->require_cap();
		$mgr = Employee_Manager::instance();
		$editing_id = isset( $_GET['edit'] ) ? (int) $_GET['edit'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$employee = null;
		$is_edit = false;
		if ( $editing_id ) {
			$is_edit = true;
			check_admin_referer( 'emp_edit_' . $editing_id );
			$employee = $mgr->get_employee( $editing_id );
			if ( ! $employee ) {
				wp_die( 'Employee not found.' );
			}
		}
		$action_url = admin_url( 'admin-post.php' );
		?>
		<div class="wrap">
			<h1><?php echo $is_edit ? 'Edit Employee' : 'Add New Employee'; ?></h1>
			<form method="post" action="<?php echo esc_url( $action_url ); ?>">
				<table class="form-table" role="presentation">
					<tbody>
						<tr>
							<th scope="row"><label for="emp-name">Name</label></th>
							<td><input name="name" type="text" id="emp-name" value="<?php echo $employee ? esc_attr( $employee['name'] ) : ''; ?>" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="emp-dept">Department</label></th>
							<td><input name="dept" type="text" id="emp-dept" value="<?php echo $employee ? esc_attr( $employee['dept'] ) : ''; ?>" class="regular-text" required></td>
						</tr>
						<tr>
							<th scope="row"><label for="emp-salary">Salary</label></th>
							<td><input name="salary" type="number" step="0.01" id="emp-salary" value="<?php echo $employee ? esc_attr( $employee['salary'] ) : ''; ?>" class="regular-text" required></td>
						</tr>
					</tbody>
				</table>
				<?php if ( $is_edit ) : ?>
					<input type="hidden" name="action" value="emp_update" />
					<input type="hidden" name="id" value="<?php echo (int) $employee['id']; ?>" />
					<?php wp_nonce_field( 'emp_update_' . (int) $employee['id'] ); ?>
					<?php submit_button( 'Update Employee' ); ?>
				<?php else : ?>
					<input type="hidden" name="action" value="emp_create" />
					<?php wp_nonce_field( 'emp_create' ); ?>
					<?php submit_button( 'Create Employee' ); ?>
				<?php endif; ?>
			</form>
		</div>
		<?php
	}

	public function handle_create() {
		$this->require_cap();
		check_admin_referer( 'emp_create' );
		$mgr = Employee_Manager::instance();
		$validated = $mgr->validate_employee_input( $_POST, false ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $validated['errors'] ) ) {
			wp_die( implode( ' ', $validated['errors'] ) );
		}
		$id = $mgr->create_employee( $validated['clean']['name'], $validated['clean']['dept'], $validated['clean']['salary'] );
		if ( is_wp_error( $id ) ) {
			wp_die( $id->get_error_message() );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=employee-manager' ) );
		exit;
	}

	public function handle_update() {
		$this->require_cap();
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $id ) {
			wp_die( 'Invalid ID.' );
		}
		check_admin_referer( 'emp_update_' . $id );
		$mgr = Employee_Manager::instance();
		$validated = $mgr->validate_employee_input( $_POST, true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! empty( $validated['errors'] ) ) {
			wp_die( implode( ' ', $validated['errors'] ) );
		}
		$fields = array_filter(
			$validated['clean'],
			function ( $v ) {
				return $v !== null && $v !== '';
			}
		);
		$updated = $mgr->update_employee( $id, $fields );
		if ( is_wp_error( $updated ) ) {
			wp_die( $updated->get_error_message() );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=employee-manager' ) );
		exit;
	}

	public function handle_delete() {
		$this->require_cap();
		$id = isset( $_POST['id'] ) ? (int) $_POST['id'] : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $id ) {
			wp_die( 'Invalid ID.' );
		}
		check_admin_referer( 'emp_delete_' . $id );
		$mgr = Employee_Manager::instance();
		$deleted = $mgr->delete_employee( $id );
		if ( is_wp_error( $deleted ) ) {
			wp_die( $deleted->get_error_message() );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=employee-manager' ) );
		exit;
	}
}


