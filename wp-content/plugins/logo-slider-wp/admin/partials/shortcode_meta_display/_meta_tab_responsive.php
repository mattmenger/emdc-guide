<?php
if (!defined('WPINC')) {
    die;
}

/********************************************************************************/
$this->meta_form->header_spacer(
    array(
        'label'     => __( 'Responsive Settings', $this->plugin_name ),
    )
);
/********************************************************************************/


$this->meta_form->number(
    array(
        'label'     => __( 'Item in Large Desktops', $this->plugin_name ),
        'desc'      => __( 'Item in Large Desktops Devices (1200px and Up).', $this->plugin_name ),
        'name'      => 'meta_lgx_lsp_shortcodes[lgx_large_desktop_item]',
        'id'        => 'lgx_large_desktop_item',
        'default'   => 5
    )
);

$this->meta_form->number(
    array(
        'label'     => __( 'Item in Desktops', $this->plugin_name ),
        'desc'      => __( 'Item in Desktops Devices (Desktops 992px and Up).', $this->plugin_name ),
        'name'      => 'meta_lgx_lsp_shortcodes[lgx_desktop_item]',
        'id'        => 'lgx_desktop_item',
        'default'   => 4
    )
);

$this->meta_form->number(
    array(
        'label'     => __( 'Item in Tablets', $this->plugin_name ),
        'desc'      => __( 'Item in Tablets Devices (768px and Up).', $this->plugin_name ),
        'name'      => 'meta_lgx_lsp_shortcodes[lgx_tablet_item]',
        'id'        => 'lgx_tablet_item',
        'default'   => 3
    )
);

$this->meta_form->number(
    array(
        'label'     => __( 'Item in Mobile', $this->plugin_name ),
        'desc'      => __( 'Item in Mobile Devices (Less than 768px).', $this->plugin_name ),
        'name'      => 'meta_lgx_lsp_shortcodes[lgx_mobile_item]',
        'id'        => 'lgx_mobile_item',
        'default'   => 2
    )
);
