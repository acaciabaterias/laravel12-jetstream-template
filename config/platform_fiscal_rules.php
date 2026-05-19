<?php

declare(strict_types=1);

return [
    'required_scenarios' => [
        'direct_export' => [
            'label' => 'Direct export',
            'operation_direction' => 'export',
        ],
        'resale_import' => [
            'label' => 'Resale import',
            'operation_direction' => 'import',
        ],
        'indirect_export' => [
            'label' => 'Indirect export',
            'operation_direction' => 'export',
        ],
        'industrial_import' => [
            'label' => 'Industrial import',
            'operation_direction' => 'import',
        ],
    ],

    'supported_directions' => ['export', 'import', 'domestic_out', 'domestic_in'],

    'fallback_rules' => [
        'default_classification_code' => 'MANUAL_REVIEW',
        'default_validation_flags' => [
            'requires_manual_review' => true,
            'requires_trade_document' => true,
        ],
        'scenarios' => [
            'direct_export' => [
                'cfop_code' => '7101',
                'classification_code' => 'MANUAL_REVIEW',
                'validation_flags' => [
                    'requires_manual_review' => true,
                    'requires_trade_document' => true,
                    'requires_foreign_partner' => true,
                ],
            ],
            'indirect_export' => [
                'cfop_code' => '7501',
                'classification_code' => 'MANUAL_REVIEW',
                'validation_flags' => [
                    'requires_manual_review' => true,
                    'requires_trade_document' => true,
                    'requires_export_commitment' => true,
                ],
            ],
            'resale_import' => [
                'cfop_code' => '3101',
                'classification_code' => 'MANUAL_REVIEW',
                'validation_flags' => [
                    'requires_manual_review' => true,
                    'requires_trade_document' => true,
                    'requires_customs_record' => true,
                ],
            ],
            'industrial_import' => [
                'cfop_code' => '3551',
                'classification_code' => 'MANUAL_REVIEW',
                'validation_flags' => [
                    'requires_manual_review' => true,
                    'requires_trade_document' => true,
                    'requires_ncm' => true,
                ],
            ],
        ],
    ],

    'events' => [
        'publish_to_backbone' => true,
        'default_consumers' => ['platform', 'fiscal', 'observability'],
    ],
];
