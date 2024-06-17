const kvapay_data = window.wc.wcSettings.getSetting( 'kvapay_data', {} );
const kvapay_label = window.wp.htmlEntities.decodeEntities( kvapay_data.title )
    || window.wp.i18n.__( 'My Gateway', 'kvapay' );
const kvapay_content = ( kvapay_data ) => {
    return window.wp.htmlEntities.decodeEntities( kvapay_data.description || '' );
};
const Kvapay = {
    name: 'kvapay',
    label: kvapay_label,
    content: Object( window.wp.element.createElement )( kvapay_content, null ),
    edit: Object( window.wp.element.createElement )( kvapay_content, null ),
    canMakePayment: () => true,
    placeOrderButtonLabel: window.wp.i18n.__( 'Continue', 'kvapay' ),
    ariaLabel: kvapay_label,
    supports: {
        features: kvapay_data.supports,
    },
};
window.wc.wcBlocksRegistry.registerPaymentMethod( Kvapay );