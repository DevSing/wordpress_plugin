<?php

class Your_Custom_Widget_Class extends \Elementor\Widget_Base {

    public function get_name() {
        return 'product_showcase_by_attribute';
    }

    public function get_title() {
        return __('Product Showcase by Attribute', 'product-showcase');
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return ['basic'];
    }

    protected function _register_controls() {
        // Content controls
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'product-showcase'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'attributes',
            [
                'label' => __('Attributes', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_attribute_options(),
                'multiple' => true,
            ]
        );
// Add this code after the 'attributes' control
$this->add_control(
    'selection_type',
    [
        'label' => __('Select By', 'product-showcase'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'default' => 'attribute',
        'options' => [
            'attribute' => __('Attribute', 'product-showcase'),
            'tag' => __('Tag', 'product-showcase'),
        ],
    ]
);

$this->add_control(
    'tags',
    [
        'label' => __('Tags', 'product-showcase'),
        'type' => \Elementor\Controls_Manager::SELECT2,
        'options' => $this->get_tag_options(),
        'multiple' => true,
        'condition' => [
            'selection_type' => 'tag',
        ],
    ]
);
        $this->add_control(
            'products_per_page',
            [
                'label' => __('Number of Products to Show', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
            ]
        );

        // Responsive Control for Columns
        $this->add_responsive_control(
            'columns',
            [
                'label' => __('Number of Columns', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'desktop_default' => 4,
                'tablet_default' => 2,
                'mobile_default' => 1,
            ]
        );

        // Responsive Control for Rows
        $this->add_responsive_control(
            'rows',
            [
                'label' => __('Number of Rows', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'desktop_default' => 2,
                'tablet_default' => 1,
                'mobile_default' => 1,
            ]
        );

        $this->add_control(
            'show_pagination',
            [
                'label' => __('Show Pagination', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_sorting',
            [
                'label' => __('Show Sorting', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'product_orderby',
            [
                'label' => __('Order Products By', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Latest Products', 'product-showcase'),
                    'title' => __('Title', 'product-showcase'),
                    'price' => __('Price', 'product-showcase'),
                    'popularity' => __('Popularity', 'product-showcase'),
                    'rating' => __('Rating', 'product-showcase'),
                    'rand' => __('Random', 'product-showcase'),
                ],
            ]
        );

        $this->add_control(
            'product_order',
            [
                'label' => __('Order', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => __('Ascending', 'product-showcase'),
                    'DESC' => __('Descending', 'product-showcase'),
                ],
            ]
        );

        $this->end_controls_section();

        // Style controls
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'product-showcase'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Product Box Style with Responsive Controls
        $this->add_responsive_control(
            'product_box_padding',
            [
                'label' => __('Product Box Padding', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Responsive Control for Product Title
        $this->add_responsive_control(
            'product_title_alignment',
            [
                'label' => __('Product Title Alignment', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'product-showcase'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'product-showcase'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'product-showcase'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'desktop_default' => 'center',
                'tablet_default' => 'center',
                'mobile_default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product h2.woocommerce-loop-product__title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'product_title_typography',
                'label' => __('Typography', 'product-showcase'),
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product h2.woocommerce-loop-product__title',
            ]
        );

        // Responsive Control for Product Price
        $this->add_responsive_control(
            'product_price_alignment',
            [
                'label' => __('Product Price Alignment', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'product-showcase'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'product-showcase'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'product-showcase'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'desktop_default' => 'center',
                'tablet_default' => 'center',
                'mobile_default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .price' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'product_price_typography',
                'label' => __('Typography', 'product-showcase'),
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .price',
            ]
        );

        // Responsive Control for Button Text Alignment
        $this->add_responsive_control(
            'product_button_text_alignment',
            [
                'label' => __('Button Text Alignment', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'product-showcase'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'product-showcase'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'product-showcase'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'desktop_default' => 'center',
                'tablet_default' => 'center',
                'mobile_default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .button' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'product_button_typography',
                'label' => __('Button Typography', 'product-showcase'),
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .button',
            ]
        );

        $this->add_control(
            'product_button_border_radius',
            [
                'label' => __('Button Border Radius', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'product_button_padding',
            [
                'label' => __('Button Padding', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Product Image Style
        $this->add_responsive_control(
            'product_image_size',
            [
                'label' => __('Product Image Size', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 500,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product img' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'product_image_border_radius',
            [
                'label' => __('Product Image Border Radius', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Additional Product Details (SKU, Categories) Style
        $this->add_control(
            'show_product_details',
            [
                'label' => __('Show Product Details (SKU, Categories)', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'default' => 'yes',
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .product-meta' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'product_meta_typography',
                'label' => __('Product Meta Typography', 'product-showcase'),
                'selector' => '{{WRAPPER}} .woocommerce ul.products li.product .product-meta',
            ]
        );

        $this->add_control(
            'product_meta_color',
            [
                'label' => __('Product Meta Color', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .product-meta' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Box Style
        $this->start_controls_section(
            'content_box_style_section',
            [
                'label' => __('Content Box', 'product-showcase'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'content_box_background_color',
            [
                'label' => __('Content Box Background Color', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .product-details' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_box_padding',
            [
                'label' => __('Content Box Padding', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .product-details' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'content_box_border_radius',
            [
                'label' => __('Content Box Border Radius', 'product-showcase'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .woocommerce ul.products li.product .product-details' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_attribute_options() {
        $attributes = wc_get_attribute_taxonomies();
        $options = [];
        foreach ($attributes as $attribute) {
            $options[wc_attribute_taxonomy_name($attribute->attribute_name)] = $attribute->attribute_label;
        }
        return $options;
    }
    private function get_tag_options() {
        $tags = get_terms('product_tag');
        $options = [];
        foreach ($tags as $tag) {
            $options[$tag->slug] = $tag->name;
        }
        return $options;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $selection_type = $settings['selection_type'];
    $attributes = $settings['attributes'];
    $tags = $settings['tags'];
        $products_per_page = $settings['products_per_page'];
        $columns = $settings['columns'];
        $rows = $settings['rows'];
        $show_pagination = $settings['show_pagination'] === 'yes';
        $show_sorting = $settings['show_sorting'] === 'yes';
        $orderby = $settings['product_orderby'];
        $order = $settings['product_order'];

        // Calculate the number of products based on rows and columns if not using the products_per_page directly
    if ($products_per_page <= 0) {
        $products_per_page = $columns * $rows;
    }

    $tax_query = [];

    if ($selection_type === 'attribute' && !empty($attributes)) {
        foreach ($attributes as $attribute) {
            $tax_query[] = [
                'taxonomy' => esc_attr($attribute),
                'field' => 'slug',
                'terms' => [], // Here you might want to allow users to select specific terms for each attribute
            ];
        }
    } elseif ($selection_type === 'tag' && !empty($tags)) {
        $tax_query[] = [
            'taxonomy' => 'product_tag',
            'field' => 'slug',
            'terms' => $tags,
        ];
    }

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $shortcode = '[products ';
    $shortcode .= 'limit="' . esc_attr($products_per_page) . '" ';
    $shortcode .= 'paginate="' . ($show_pagination ? 'true' : 'false') . '" ';
    $shortcode .= 'columns="' . esc_attr($columns) . '" '; // Use the columns control
    $shortcode .= 'page="' . esc_attr($paged) . '" ';
    $shortcode .= 'orderby="' . esc_attr($orderby) . '" ';
    $shortcode .= 'order="' . esc_attr($order) . '" ';

    if ($selection_type === 'attribute') {
        foreach ($tax_query as $query) {
            $shortcode .= 'attribute="' . $query['taxonomy'] . '" terms="' . implode(',', $query['terms']) . '" ';
        }
    } elseif ($selection_type === 'tag') {
        $shortcode .= 'tag="' . implode(',', $tags) . '" ';
    }

    $shortcode .= ']';

    // Apply sorting if enabled
    if ($show_sorting) {
        add_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    } else {
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
    }

    echo do_shortcode($shortcode);
}
}