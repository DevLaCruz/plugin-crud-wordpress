jQuery(document).ready(function($) {
    // Actualizar registro
    $(document).on('click', '#update-button', function() {
        var updateId = $('[name="update_id"]').val();
        var nombre = $('#nombre').val();
        var apellido = $('#apellido').val();
        var sexo = $('#sexo').val();

        // Validar los campos
        if (nombre.trim() === '') {
            alert('Por favor, ingresa un nombre válido.');
            return;
        }

        if (apellido.trim() === '') {
            alert('Por favor, ingresa un apellido válido.');
            return;
        }

        $.ajax({
            url: myCrudPlugin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'my_crud_plugin_update_record',
                update_id: updateId,
                nombre: nombre,
                apellido: apellido,
                sexo: sexo
            },
            success: function(response) {
                $('#crud-results').html(response);
                location.reload(); // Recargar la página
            }
        });
    });



    // Crear registro
    $('#create-button').on('click', function() {
        var nombre = $('#nombre').val();
        var apellido = $('#apellido').val();
        var sexo = $('#sexo').val();
        
        // Validar los campos
        if (nombre.trim() === '') {
            alert('Por favor, ingresa un nombre válido.');
            return;
        }

        if (apellido.trim() === '') {
            alert('Por favor, ingresa un apellido válido.');
            return;
        }

        $.ajax({
            url: myCrudPlugin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'my_crud_plugin_create_record',
                nombre: nombre,
                apellido: apellido,
                sexo: sexo
            },
            success: function(response) {
                $('#crud-results').html(response);
                $('#nombre, #apellido').val('');
            }
        });
        // Resto del código para crear un registro
    });

    // Editar registro
    $(document).on('click', '.edit-button', function() {
        var recordId = $(this).data('id');

        $.ajax({
            url: myCrudPlugin.ajaxUrl,
            type: 'POST',
            data: {
                action: 'my_crud_plugin_get_record',
                id: recordId
            },
            success: function(response) {
                $('#my-crud-form').html(response);
            }
        });
    });

    // Eliminar registro
    $(document).on('click', '.delete-button', function() {
        if (confirm('¿Estás seguro de eliminar este registro?')) {
            var recordId = $(this).data('id');

            $.ajax({
                url: myCrudPlugin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'my_crud_plugin_delete_record',
                    id: recordId
                },
                success: function(response) {
                    $('#crud-results').html(response);
                }
            });
        }
    });
});
