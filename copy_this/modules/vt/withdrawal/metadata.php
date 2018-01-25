<?php

/**
 * [vt] Withdrawal Form for OXID eShop
 * Copyright (C) 2017 Marat Bedoev
 * info:  m@marat.ws
 *
 * This program is free software;
 * you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation;
 * either version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
 *
 * author: Marat Bedoev
 */

$sMetadataVersion = '1.1';
$aModule = [
    'id'          => 'withdrawal',
    'title'       => [
        'de' => '[vt] Widerrufsformular',
        'en' => '[vt] Withdrawal Form'
    ],
    'description' => 'das etwas intelligentere Widerrufsformular',
    'thumbnail'   => '../oxid-vt.jpg',
    'version'     => '0.0.1 (2018-01-25)',
    'author'      => 'Marat Bedoev',
    'email'       => 'm@marat.ws',
    'url'         => 'https://github.com/vanilla-thunder/oxid-module-withdrawal-form',
    'extend'      => [ 'oxemail' => 'vt/withdrawal/application/extend/oxemailwithdrawal' ],
    'files'       => [ 'withdrawalform' => 'vt/withdrawal/application/controllers/withdrawalform.php' ],
    'templates'   => [
        'withdrawalform.tpl'       => 'vt/withdrawal/application/views/withdrawalform.tpl',
        'withdrawalEmailHtml.tpl'  => 'vt/withdrawal/application/views/withdrawalEmailHtml.tpl',
        'withdrawalEmailPlain.tpl' => 'vt/withdrawal/application/views/withdrawalEmailPlain.tpl'
    ],
    'settings'    => [
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalSitekey',
            'type'     => 'str',
            'value'    => '',
            'position' => 0
        ],
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalSecret',
            'type'     => 'str',
            'value'    => '',
            'position' => 1
        ],
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalReasons',
            'type'     => 'aarr',
            'value'    => [
                'small'   => 'zu klein',
                'large'   => 'zu groß',
                'dislike' => 'nicht gefallen'
            ],
            'position' => 2
        ],
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalEmail',
            'type'     => 'str',
            'value'    => '',
            'position' => 3
        ],
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalCC',
            'type'     => 'arr',
            'value'    => [],
            'position' => 4
        ],
        [
            'group'    => 'vtWithdrawalMain',
            'name'     => 'vtWithdrawalRetoureportal',
            'type'     => 'str',
            'value'    => '',
            'position' => 5
        ]
    ]
];
