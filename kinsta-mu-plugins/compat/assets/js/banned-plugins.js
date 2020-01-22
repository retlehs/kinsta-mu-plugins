jQuery( function( $ ) {

  var pluginFilter = $( '#plugin-filter' );
  var theList = $( '#the-list' );
  var MutationObserver;
  var observe;

  /**
   * Handle the plugins table list.
   *
   * @return {Void}
   */
  function pluginsPage() {

    var disabledPluginRow;
    var index = 0;

    if ( ! Array.isArray( kinstaDisabledPlugins ) || 0 === kinstaDisabledPlugins.length ) {
      return;
    }

    for ( index; index < kinstaDisabledPlugins.length; index++ ) {
      disabledPlugin = kinstaDisabledPlugins[ index ];
      disabledPluginRow = theList.find( '[data-slug="' + disabledPlugin + '"]' );
      if ( 0 === disabledPluginRow.length ) {
        disabledPluginRow = theList.find( '[data-plugin^="' + disabledPlugin + '"]' );
      }
      disabledPluginRow.find( '.check-column' ).empty();
    }
  }

  /**
   * Modify the plugin card element to prevent installation.
   *
   * @return {Void}
   */
  function pluginInstallPage() {

    var kinstaBannedPlugins;
    var bannedPlugin;
    var index = 0;

    if ( ! Array.isArray( kinstaDisabledPlugins ) || 0 === kinstaWarningPlugins.length ) { // If kinstaDisabledPlugins not an array, abort!
      return;
    }

    kinstaBannedPlugins = kinstaWarningPlugins.concat( kinstaDisabledPlugins );

    for ( index; index < kinstaBannedPlugins.length; index++ ) {

      bannedPlugin = kinstaBannedPlugins[ index ];
      bannedPluginCard = pluginFilter.find( '.plugin-card-' + bannedPlugin );

      if ( 1 === bannedPluginCard.length ) {
        bannedPluginCard.addClass( 'kinsta-banned-plugin' );
        bannedPluginCardUrl = bannedPluginCard.find( '.thickbox.open-plugin-details-modal' );
        bannedPluginCardUrl.replaceWith( function() {
          return $( '<span/>', { 'class': 'kinsta-banned-plugin__title' }).append( $( this ).contents() );
        });
      }
    }
  }

  if ( 'plugins-php' === adminpage && 1 === theList.length ) {
    try {
      pluginsPage();
    } catch ( error ) {
      console.warn( error );
    }
  }

  // Roll out.
  if ( 'plugin-install-php' === adminpage && 1 === pluginFilter.length ) {
    try {
      MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
      observe = new MutationObserver( function( mutations ) {
        mutations.forEach( function( mutation ) {
          if ( 'childList' === mutation.type && '' !== pluginFilter.html().trim() ) {
            pluginInstallPage();
          }
        });
      });
      observe.observe( pluginFilter.get( 0 ), {
        childList: true
      });
      pluginInstallPage();
    } catch ( error ) {
      console.warn( error );
    }
  }
});
