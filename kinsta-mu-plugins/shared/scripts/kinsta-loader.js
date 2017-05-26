jQuery.each( jQuery( '.kinsta-loader' ), function( key, loader ){
    var element =  jQuery(loader);
    var initialText = !element.val() ? element.text() : element.val();
    var completedText = element.attr('data-completedText');
    var progressText = element.attr('data-progressText');
    element.attr( 'data-backgroundColor', element.css('backgroundColor') );
    var type = element.attr('data-type');
    var textElement = jQuery( '<span class="text">' + initialText + '</span>' );
    var progressElement = jQuery( '<div class="progress"><div class="progress-bar"></div></div>' );

    element.html( '' ).append(textElement).append(progressElement);
    element.bind( 'click', function() {
        var loader = jQuery(this);
        if( loader.hasClass('loading') ) {
            return;
        }
        loader.addClass('loading');
        var progressBar = loader.find('.progress-bar');
        var progress = loader.find('.progress');
        var text = loader.find('.text');
        var originalColor = loader.attr( 'data-backgroundColor' );
        text.css( { minWidth: text.width() + 'px'} );
        text.text( progressText )
        var speed = type != 'reload' ? 1200 : 3000;
        progressBar.animate({ width: '100%' }, speed, 'linear', function(){
            if( type != 'reload') {
                text.fadeOut( function() {
                    loader.animate( {backgroundColor: '#8DE382'} )
                    text.text(completedText).fadeIn();
                    window.setTimeout( function() {
                        progress.animate( { bottom: "-20px"} )
                        text.fadeOut( function() {
                            loader.animate( {backgroundColor: originalColor}, function(){
                                loader.removeClass('loading');
                                loader.removeAttr( 'style' )
                            })
                            text.text(initialText).fadeIn( function() {
                                progressBar.css({ width: '0px'})
                                progress.css({ bottom: '0px'})
                            });
                        })
                    }, 2000 );

                })
            }
        })
    })
})
