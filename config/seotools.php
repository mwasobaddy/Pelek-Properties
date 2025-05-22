<?php
/**
 * @see https://github.com/artesaos/seotools
 */
return [
    'inertia' => env('SEO_TOOLS_INERTIA', false),
    'meta' => [
        /*
         * The default configurations to be used by the meta generator.
         */
        'defaults'       => [
            'title'        => "Pelek Properties - Premier Real Estate in Kenya", // set false to total remove
            'titleBefore'  => false, // Put defaults.title before page title
            'description'  => 'Discover luxury properties, rentals, and commercial spaces across Kenya. Expert property management and valuation services.', // set false to total remove
            'separator'    => ' | ',
            'keywords'     => ['real estate kenya', 'property management', 'luxury homes', 'commercial properties', 'rental properties', 'property valuation'],
            'canonical'    => 'current', // Set to null or 'full' to use Url::full(), set to 'current' to use Url::current(), set false to total remove
            'robots'       => 'all', // Set to 'all', 'none' or any combination of index/noindex and follow/nofollow
        ],
        /*
         * Webmaster tags are always added.
         */
        'webmaster_tags' => [
            'google'    => null,
            'bing'      => null,
            'alexa'     => null,
            'pinterest' => null,
            'yandex'    => null,
            'norton'    => null,
        ],

        'add_notranslate_class' => false,
    ],
    'opengraph' => [
        /*
         * The default configurations to be used by the opengraph generator.
         */
        'defaults' => [
            'title'       => 'Pelek Properties - Premier Real Estate in Kenya', // set false to total remove
            'description' => 'Discover luxury properties, rentals, and commercial spaces across Kenya. Expert property management and valuation services.', // set false to total remove
            'url'         => 'current', // Set null for using Url::current(), set false to total remove
            'type'        => 'website',
            'site_name'   => 'Pelek Properties',
            'images'      => ['/images/logo.png'],
        ],
    ],
    'twitter' => [
        /*
         * The default values to be used by the twitter cards generator.
         */
        'defaults' => [
            'card'        => 'summary_large_image',
            'site'        => '@PelekProperties',
            'title'       => 'Pelek Properties - Premier Real Estate in Kenya',
            'description' => 'Discover luxury properties, rentals, and commercial spaces across Kenya. Expert property management and valuation services.',
            'image'       => '/images/logo.png',
        ],
    ],
    'json-ld' => [
        /*
         * The default configurations to be used by the json-ld generator.
         */
        'defaults' => [
            'title'       => 'Pelek Properties - Premier Real Estate in Kenya', // updated from placeholder
            'description' => 'Discover luxury properties, rentals, and commercial spaces across Kenya. Expert property management and valuation services.', // updated from placeholder
            'url'         => 'current', // Set to current URL
            'type'        => 'RealEstateAgent', // Changed to match real estate business
            'images'      => ['/images/logo.png'],
        ],
    ],
];
