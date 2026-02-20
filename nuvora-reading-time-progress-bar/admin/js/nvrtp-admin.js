( function () {
    var btn = document.getElementById( 'nvrtp-reset-btn' );
    if ( ! btn ) return;
    btn.addEventListener( 'click', function ( e ) {
        if ( ! window.confirm( nvrtpAdminVars.resetConfirm ) ) {
            e.preventDefault();
        }
    } );
} )();