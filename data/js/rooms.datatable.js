$( document ).ready( function () {
    var $roomFormDiv = $( '<div id="room-form-div"></div>' ).appendTo( 'body' );

    toastr.options = {
        closeButton  : true,
        progressBar  : true,
        showMethod   : 'slideDown',
        positionClass: 'toast-top-center',
        timeOut      : 5000
    };

    $( '#rooms-data-table' ).dt( { updateUrl: '/rooms', deleteUrl: '/rooms/delete', columns: 5 } );

    $( document ).on( 'click', 'button[data-action="room-delete"]', function ( event ) {
        var id = $( this ).data( 'id' );

        var $tr       = $( 'tr[data-id="' + id + '"]' ),
            prevIndex = $tr.prev( 'tr' ).index();

        $( '#rooms-data-table' ).dt( 'showDeleteConfirmationRow', id, prevIndex, null/* { deleteUrl: '/rooms/delete', columns: 5 }*/ );
    } ).on( 'click', 'button[data-action="add-room"]', function ( event ) {
        $.ajax( {
            url     : '/rooms/create',
            dataType: 'html',
            success : function ( data ) {
                $roomFormDiv.modalWindow( {
                    title  : 'Добавить кабинет',
                    content: data,
                    size   : 'large',
                    buttons: {
                        save  : {
                            title     : 'Сохранить',
                            attributes: {
                                'data-action': 'save-room-form',
                            }
                        },
                        cancel: {
                            title  : 'Отменить',
                            'class': 'btn btn-danger',
                            attributes: {
                                'id': 'button-cancel-room'
                            }
                        }
                    }
                } ).modal( 'show' );
            }
        } );
    } ).on( 'click', 'button[data-action="room-edit"]', function ( event ) {
        var id = $( this ).data( 'id' );

        $.ajax( {
            url     : '/rooms/update/' + id,
            dataType: 'html',
            success : function ( data ) {
                $roomFormDiv.modalWindow( {
                    title  : 'Редактировать кабинет',
                    content: data,
                    size   : 'large',
                    buttons: {
                        save  : {
                            title     : 'Сохранить',
                            attributes: {
                                'data-action': 'save-room-form',
                            }
                        },
                        cancel: {
                            title  : 'Отменить',
                            'class': 'btn btn-danger',
                        }
                    }
                } ).modal( 'show' );
            }
        } );
    } ).on( 'click', 'button[data-action="save-room-form"]', function ( event ) {
        var id       = $( this ).parents( '.modal-content' ).find( 'form #room-id' ).val(),
            formData = $( this ).parents( '.modal-content' ).find( 'form' ).serializeArray(),
            href;

        if ( id ) {
            href = '/rooms/update/' + id;
        } else {
            href = '/rooms/create';
        }

        $.ajax( {
            url     : href,
            data    : formData,
            type    : 'POST',
            dataType: 'json',
            success : function ( data ) {
                if ( data.status === true ) {
                    $roomFormDiv.modal( 'hide' );
                    toastr.success( data.message );
                    // $( '#rooms-data-table-wrapper' ).dt( 'reloadDataTable', '/rooms' );
                    var requestData = $( '#rooms-data-table' ).find( 'tr.filter input, tr.filter select' ).serializeArray(),
                        updatePart;

                    if(requestData.length){
                        updatePart = 'tbody';
                    }else{
                        updatePart = 'wrapper';
                    }

                    $( '#rooms-data-table' ).dt( 'reloadDataTable', requestData, updatePart );
                } else {
                    var message = ( typeof data.message === 'object' ) ? '' : data.message,
                        $form   = $roomFormDiv.find( 'form' );

                    if ( message ) {
                        swal( 'Ошибка!', message, "error" );
                    }

                    $form.find( '.form-group' ).removeClass( 'has-error' );
                    $form.find( '.help-block' ).remove();

                    if ( typeof data.message === 'object' ) {
                        $.each( data.message, function ( field, msgs ) {
                            $.each( msgs, function ( key, msg ) {
                                $form.find('[name^="' + field + '"]').parents('.form-group').addClass('has-error');
                                $form.find('[name^="' + field + '"]').after('<div class="help-block">' + msg + '</div>');
                            } );
                        } );
                    }
                }
            }
        } );
    } );
} );