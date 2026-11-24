const sepordeh_settings = window.wc.wcSettings.getSetting("WC_SpDeh_data", {});

const sepordeh_label =
  window.wp.htmlEntities.decodeEntities(sepordeh_settings.title) ||
  window.wp.i18n.__("پرداخت آنلاین سپرده", "woocommerce");


const sepordeh_label_with_icon = window.wp.element.createElement(
  "span",
  {
    style: {
      display: "inline-flex",
      alignItems: "center",
      gap: "10px",
      direction: "rtl",
    },
  },
  window.wp.element.createElement("img", {
    src: sepordeh_settings.icon,
    alt: sepordeh_label,
    style: { height: "30px" },
  }),
  sepordeh_label,
);
const sepordeh_Content = () => {
  return window.wp.htmlEntities.decodeEntities(
    sepordeh_settings.description ||
      "پرداخت امن به وسیله کلیه کارت های عضو شتاب از طریق درگاه سپرده"
  );
};

// تعریف گیت‌وی پرداخت برای WooCommerce Blocks
const Sepordeh_Block_Gateway = {
  name: "WC_SpDeh",
  label: sepordeh_label_with_icon,
  content: window.wp.element.createElement(sepordeh_Content, null),
  edit: window.wp.element.createElement(sepordeh_Content, null),
  canMakePayment: () => true,
  ariaLabel: sepordeh_label,
  supports: {
    features: sepordeh_settings.supports,
  },
};
window.wc.wcBlocksRegistry.registerPaymentMethod(Sepordeh_Block_Gateway);
