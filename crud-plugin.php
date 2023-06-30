<?php
/*
Plugin Name: CRUD de Personas
Author: Alejandro De La Cruz
*/

// Función para crear la tabla en la activación del plugin
function my_crud_plugin_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';



    // Verificar si la tabla ya existe
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Crear la tabla con los campos necesarios
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nombre varchar(100) NOT NULL,
            apellido varchar(100) NOT NULL,
            sexo varchar(10) NOT NULL,
            PRIMARY KEY (id)
        ) ";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}
register_activation_hook( __FILE__, 'my_crud_plugin_create_table' );

// Función para agregar la página de administración del plugin
function my_crud_plugin_admin_menu() {
    add_menu_page(
        'Gestión de Datos',
        'Gestión de Usuarios',
        'manage_options',
        'my-crud-plugin',
        'my_crud_plugin_page_content',
        'dashicons-admin-generic',
        80
    );

    // Agregar el archivo JavaScript necesario
    wp_enqueue_script('my-crud-plugin-script', plugins_url('/js/crud-plugin.js', __FILE__), array('jquery'));
    wp_enqueue_style('my-crud-plugin-style', plugins_url('/css/crud-plugin.css', __FILE__));
wp_localize_script('my-crud-plugin-script', 'myCrudPlugin', array('ajaxUrl' => admin_url('admin-ajax.php')));
wp_enqueue_script('jquery-validation', 'https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js', array('jquery'));

}
add_action('admin_menu', 'my_crud_plugin_admin_menu');

// Función para cargar el contenido de la página de administración
function my_crud_plugin_page_content() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';
    ?>
    <div class="wrap">
        <h1>Registro de Personas</h1>

        <form id="my-crud-form" method="post">
            <h2>Crear Registro</h2>
            <div style="display:flex;">
            <div>
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required><br>
            <br>
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required><br>
            </div>
            <div style="margin-left:50px;">
            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
                <option value="M">Masculino</option>
                <option value="F">Femenino</option>
            </select><br>
            <br>
            <input type="button" id="create-button" class="boton" value="Crear Registro">
            </div>
            </div>
        </form>

        <h2>Resultados</h2>
        <div id="crud-results">
            <?php echo my_crud_plugin_get_records_html(); ?>
        </div>
    </div>
    <?php
}

// Función para obtener los registros de la tabla y generar el HTML
function my_crud_plugin_get_records_html() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';

    // Obtener todos los registros de la tabla
    $records = $wpdb->get_results("SELECT * FROM $table_name");

    // Generar el HTML de la tabla de resultados
    ob_start();
    if ($records) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
        <th style="color: white; background-color: #222;">ID</th>
        <th style="color: white; background-color: #222;">Nombre</th>
        <th style="color: white; background-color: #222;">Apellido</th>
        <th style="color: white; background-color: #222;">Sexo</th>
        <th style="color: white; background-color: #222;">Acciones</th>
        </tr></thead>';
        echo '<tbody>';
        foreach ($records as $record) {
            echo '<tr>';
            echo '<td>' . $record->id . '</td>';
            echo '<td>' . $record->nombre . '</td>';
            echo '<td>' . $record->apellido . '</td>';
            echo '<td>' . $record->sexo . '</td>';
            echo '<td>';
           
            echo '<button class="edit-button boton" data-id="' . $record->id . '">Editar</button>';
            echo '<button class="delete-button boton" style="margin-left: 5px;" data-id="' . $record->id . '">Eliminar</button>';
            
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'No se encontraron registros.';
    }
    return ob_get_clean();
}

// Función para procesar el formulario y crear un nuevo registro
function my_crud_plugin_process_form() {
    // No se necesita procesar el formulario en el lado del servidor
}
// Elimina la acción existente para procesar el formulario
remove_action('admin_init', 'my_crud_plugin_process_form');

// Agregar la acción para procesar las operaciones CRUD mediante JavaScript
add_action('admin_footer', 'my_crud_plugin_process_form_js');
function my_crud_plugin_process_form_js() {
    ?>

    <?php
}

// Agregar las acciones para manejar las operaciones CRUD mediante AJAX
add_action('wp_ajax_my_crud_plugin_create_record', 'my_crud_plugin_create_record');
add_action('wp_ajax_my_crud_plugin_get_record', 'my_crud_plugin_get_record');
add_action('wp_ajax_my_crud_plugin_delete_record', 'my_crud_plugin_delete_record');

// Función para crear un nuevo registro
function my_crud_plugin_create_record() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $sexo = $_POST['sexo'];

    // Insertar el nuevo registro en la tabla
    $wpdb->insert(
        $table_name,
        array(
            'nombre' => $nombre,
            'apellido' => $apellido,
            'sexo' => $sexo,    
        )

        
    );
    // Actualizar la tabla de resultados
    echo my_crud_plugin_get_records_html();

    wp_die(); // Terminate AJAX request
}

// Función para obtener un registro para editar
function my_crud_plugin_get_record() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';

    $record_id = $_POST['id'];

    // Obtener los datos del registro a editar
    $record = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $record_id");

    if ($record) {
        ob_start();
        ?>
        <h2>Editar Registro</h2>
        <form id="my-crud-form" method="post">
        <div style="display:flex;">
            <div>
            <input type="hidden" name="update_id" value="<?php echo $record->id; ?>">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo $record->nombre; ?>" required><br>
            <br>
            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" value="<?php echo $record->apellido; ?>" required><br>
            <br>
            </div>
            <div style="margin-left:50px;">
            <label for="sexo">Sexo:</label>
            <select id="sexo" name="sexo" required>
                <option value="M" <?php selected('M', $record->sexo); ?>>Masculino</option>
                <option value="F" <?php selected('F', $record->sexo); ?>>Femenino</option>
            </select><br>
            <br>
            <input type="button" style="width: 130px;" id="update-button" class="boton" value="Actualizar Registro">
            </div>
        </form>
        <?php
        echo ob_get_clean();
    } else {
        echo 'El registro no existe.';
    }

    wp_die(); // Terminate AJAX request
}

// Función para actualizar un registro
function my_crud_plugin_update_record() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';

    $update_id = $_POST['update_id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $sexo = $_POST['sexo'];

    // Actualizar el registro en la tabla
    $wpdb->update(
        $table_name,
        array(
            'nombre' => $nombre,
            'apellido' => $apellido,
            'sexo' => $sexo,
        ),
        array('id' => $update_id)
    );

    // Actualizar la tabla de resultados
    echo my_crud_plugin_get_records_html();

    wp_die(); // Terminate AJAX request
}

// Función para eliminar un registro
function my_crud_plugin_delete_record() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'crud';

    $delete_id = $_POST['id'];

    // Eliminar el registro de la tabla
    $wpdb->delete(
        $table_name,
        array('id' => $delete_id)
    );

    // Actualizar la tabla de resultados
    echo my_crud_plugin_get_records_html();

    wp_die(); // Terminate AJAX request
}

// Agregar la acción para procesar la actualización de registros mediante JavaScript
add_action('admin_footer', 'my_crud_plugin_update_record_js');
function my_crud_plugin_update_record_js() {
    ?>

    <?php
}

// Agregar la acción para manejar las operaciones CRUD mediante AJAX
add_action('wp_ajax_my_crud_plugin_update_record', 'my_crud_plugin_update_record');