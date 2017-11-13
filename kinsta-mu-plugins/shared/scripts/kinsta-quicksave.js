(function($) {

    $.kinstaQuicksave = function( element ) {

        var plugin = this;
        var element = $(element);
        var control = element.find('.kinsta-control');
        var controlType = control.attr( 'type' );
        var elementName = control.attr('name');
        var optionName = element.data('option-name');
        var nonce = element.find( 'input[name="kinsta-nonce"]' ).val();
        var label = element.find('.kinsta-label');
        var t;

        var messageTemplate = $('<span class="kinsta-quicksave-message"></span>');

        plugin.init = function() {
            var textControls = [ 'colors', 'date', 'datetime', 'datetime-local', 'email', 'month', 'number', 'range', 'search', 'tel', 'time', 'url', 'week', 'text', 'password' ];

            if( controlType === 'checkbox' || controlType === 'radio' || control.prop('tagName') === 'SELECT' ) {
                element.bind( 'change', function() { plugin.initiateQuickSave() } );
            }
            else if( textControls.indexOf( controlType ) !== -1 ) {
                element.bind( 'keyup', function() { plugin.deferSave() } );
            }
        }
        plugin.init();

        plugin.getValue = function() {
            if( controlType === 'checkbox' || controlType === 'radio' ) {
                if( $(control).is(':checked') ) {
                    return 1;
                }
                else {
                    return 0;
                }
            }

            return control.val();
        }

        plugin.deferSave = function() {
            if ( t ) {
                clearTimeout( t );
                t = setTimeout( plugin.initiateQuickSave , 1000 );
            }
            else {
                t = setTimeout( plugin.initiateQuickSave, 1000 );
            }
        }

        plugin.initiateQuickSave = function() {

            var check = plugin.checkValue();

            if( check === false ) {
                return false;
            }

            plugin.quickSave();

        }

        plugin.quickSave = function() {
            var data = {
                name: elementName,
                value: plugin.getValue(),
                nonce: nonce,
                action: 'kinsta_save_option',
                option_name: optionName
            }

            $.ajax({
                url: ajaxurl,
                data: data,
                type: 'post',
                success: function( result ) {
                    plugin.showMessage( 'success', 'saved', true );
                }
            })

        }

        plugin.checkValue = function() {
            if( element.hasClass( 'kinsta-number-field' ) ) {
                var value = Number(plugin.getValue());
                if( isNaN( value ) ) {
                    plugin.showMessage( 'error', 'Please enter a valid number' );
                    return false;
                }
                else {
                    plugin.clearMessages();
                }
            }

            return true;
        }

        plugin.clearMessages = function( fade ) {
            element.find( '.kinsta-quicksave-message' ).remove();
        }

        plugin.showMessage = function( type, text, fadeOut ) {
            var message = messageTemplate.clone();
            plugin.clearMessages();
            message.text( text );
            message.addClass('kinsta-quicksave-' + type );
            message.hide();
            label.append(message)
            message.fadeIn();

            if( fadeOut === true ) {
                setTimeout( function() {
                    message.fadeOut( function() {
                        message.remove();
                    })
                }, 1200)
            }
        }



    };

    $.fn.kinstaQuicksave = function() {

		return this.each(function() {
			if (undefined === $(this).data('kinstaQuicksave')) {
				var plugin = new $.kinstaQuicksave( this );
				$(this).data('kinstaQuicksave', plugin);
			}
        });

    };


    $.each( $('.kinsta-quicksave'), function( i, element ) {
        $(element).kinstaQuicksave();
    })


})(jQuery);
