'use strict';

( function( $ ) {
  $.each( $( '.kinsta-loader' ), function( key, loader ) {

    var element =  $( loader );
    var initialText = ! element.val() ? element.text() : element.val();
    var completedText = element.attr( 'data-completedText' );
    var progressText = element.attr( 'data-progressText' );
    var type = element.attr( 'data-type' );
    var textElement = $( '<span class="text">' + initialText + '</span>' );
    var progressElement = $( '<div class="progress"><div class="progress-bar"></div></div>' );

      element.attr( 'data-backgroundColor', element.css( 'backgroundColor' ) );
      element.html( '' ).append( textElement ).append( progressElement );
      element.bind( 'click', function() {

      var loader = $( this );
      var progressBar = loader.find( '.progress-bar' );
      var progress = loader.find( '.progress' );
      var text = loader.find( '.text' );
      var originalColor = loader.attr( 'data-backgroundColor' );
      var speed = 'reload' != type ? 1200 : 3000;

      if ( loader.hasClass( 'loading' ) ) {
        return;
      }

      loader.addClass( 'loading' );

      text.css({ minWidth: text.width() + 'px'});
      text.text( progressText );

      progressBar.animate({ width: '100%' }, speed, 'linear', function() {
        if ( 'reload' != type ) {
          text.fadeOut( function() {

            loader.animate({
              backgroundColor: '#8DE382',
              borderColor: '#8DE382'
            });

            text.text( completedText ).fadeIn();

            window.setTimeout( function() {
              progress.animate({ bottom: '-20px'});
              text.fadeOut( function() {
                loader.animate({
                  backgroundColor: originalColor,
                  borderColor: originalColor
                }, function() {
                  loader.removeClass( 'loading' );
                  loader.removeAttr( 'style' );
                });
                text.text( initialText ).fadeIn( function() {
                  progressBar.css({ width: '0px'});
                  progress.css({ bottom: '0px'});
                });
              });
            }, 2000 );
          });
        }
      });
    });
  });
}( jQuery ) );
