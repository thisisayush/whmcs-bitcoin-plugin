<link rel="stylesheet" type="text/css" href="{$WEB_ROOT}/modules/gateways/blockonomics/assets/css/order.css">

<div class="bnomics-order-container">
  <div class="bnomics-select-container">
    <h2>{$_BLOCKLANG.payWith}</h2>
    <table width="100%">
      {foreach $active_currencies as $code=>$crypto}
        <tr class="bnomics-select-options">
          <td align="left">
            <a href="{$WEB_ROOT}/modules/gateways/blockonomics/payment.php?show_order={$order_hash}&crypto={$code}" style="color: inherit; text-decoration: inherit;">
              <img src="{$WEB_ROOT}/modules/gateways/blockonomics/assets/img/{$code}.png" class="rotateimg{$code}" alt="{$code} Logo">
              <h3>{$crypto['name']}</h3>
              <span class="bnomics-select-currency-button">
                <button 
                  type="button"
                  class="btn btn-lg bnomics-select-currency-code"
                >{$code}</button>
              </span>
            </a>
          </td>
        </tr>
      {/foreach}
    </table>
  </div>
</div>
