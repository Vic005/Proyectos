<?php
/**
 * HANDLERS DE AJAX PARA EL GESTOR DE PROYECTOS REMOTO (PM)
 *
 * Estas funciones manejan las peticiones asíncronas (AJAX) 
 * que vienen del archivo app.html. 
 * Almacenan y recuperan los datos en la tabla wp_options de WordPress.
 */

// 1. Hook para cargar los datos (la acción 'pm_load_data' se llama desde el JavaScript)
add_action('wp_ajax_pm_load_data', 'pm_handle_load_data');

/**
 * Función que maneja la solicitud de carga de datos de proyectos.
 */
function pm_handle_load_data() {
    // === SEGURIDAD BÁSICA: Solo permitir a usuarios logueados ===
    if (!is_user_logged_in()) {
        wp_send_json_error('Error: No autorizado. Debes iniciar sesión en WordPress.');
        wp_die();
    }

    // Obtener los datos almacenados (que es un string JSON). 
    // Por defecto, devuelve '[]' si no hay datos guardados previamente.
    $stored_data = get_option('project_manager_data', '[]');
    
    // Devolver una respuesta JSON con éxito y los datos
    wp_send_json_success($stored_data);
    
    wp_die(); // Finalizar la ejecución
}

// 2. Hook para guardar los datos (la acción 'pm_save_data' se llama desde el JavaScript)
add_action('wp_ajax_pm_save_data', 'pm_handle_save_data');

/**
 * Función que maneja la solicitud de guardado de datos de proyectos.
 */
function pm_handle_save_data() {
    // === SEGURIDAD BÁSICA: Solo permitir a usuarios logueados ===
    if (!is_user_logged_in()) {
        wp_send_json_error('Error: No autorizado. Debes iniciar sesión en WordPress.');
        wp_die();
    }
    
    // Verificar si se enviaron los datos 'projects_data'
    if (isset($_POST['projects_data'])) {
        // Sanitizar el string JSON antes de guardarlo.
        $projects_data_json = sanitize_textarea_field(wp_unslash($_POST['projects_data']));
        
        // Guardar el string JSON completo. 
        // 'yes' es opcional, pero indica que se cargue automáticamente si es necesario.
        $success = update_option('project_manager_data', $projects_data_json, 'yes');
        
        if ($success) {
            wp_send_json_success('Datos guardados exitosamente.');
        } else {
            // Esto ocurre a menudo si los datos no cambiaron (WordPress no actualiza la DB)
            wp_send_json_error('Fallo al actualizar o no hubo cambios en los datos enviados.');
        }
    } else {
        wp_send_json_error('Error: Datos de proyecto no recibidos en la solicitud.');
    }

    wp_die(); // Finalizar la ejecución
}
