<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
$software_agreement = <<<'AGREEMENT'
ThinkCMF Software Use Agreement

Copyright ©2013-{:copyright_date}, ThinkCMF open source community

Thank you for choosing ThinkCMF content management framework, and hope our products can help you develop your website faster, better and stronger!

ThinkCMF is released under the MIT open source agreement and is free to use.

The ThinkCMF website building system was initiated and open sourced by Simplewind Network Technology (hereinafter referred to as Simplewind, the official website http://www.thinkcmf.com).
Simplewind Network Technology includes the following websites:
ThinkCMF official website: http://www.thinkcmf.com

ThinkCMF disclaimer
     1. ThinkCMF official does not assume any responsibility for any information content of the website constructed by ThinkCMF and any copyright disputes and legal disputes and consequences caused by it.
     2. Once you install and use ThinkCMF, you are deemed to fully understand and accept the terms of this agreement. While enjoying the rights granted by the above terms, you are subject to relevant constraints and restrictions.
AGREEMENT;

$finished_tip = <<<'FINISHED'
For the safety of your site, you have two ways to delete the installer after the installation is complete:<br>
1. Delete the "vendor/thinkcmf/cmf-install" folder;<br>
2、It is best to execute "composer remove thinkcmf/cmf-install" to delete!<br>
Please also make a backup of the "data/config/database.php" file to prevent loss!
FINISHED;

return [
    'ACCEPT'                   => 'ACCEPT',
    'SITE_HAS_INSTALLED'       => 'Website has been installed already.',
    'DIRECTORY_CANNOT_WRITE'   => 'The directory {:directory} is not writable!',
    'INSTALLATION'             => ' installation',
    'INSTALL_WIZARD'           => 'Installation wizard',
    'SOFTWARE_AGREEMENT'       => $software_agreement,
    'ENVIRONMENT_DETECT'       => 'Environmental test',
    'CREATE_DATA'              => 'Create data',
    'INSTALL_FINISH'           => 'Finish installation',
    'RECOMMENDED_CONFIG'       => 'Recommended',
    'CURRENT_STATE'            => 'Current state',
    'REQUIREMENTS'             => 'Minimum requirements',
    'OS'                       => 'Operating system',
    'UNIX_LIKE'                => 'UNIX like',
    'UNLIMITED'                => 'Not limited',
    'PHP_VERSION'              => 'PHP version',
    'MODULE_DETECT'            => 'Module detection',
    'ENABLE'                   => 'Enable',
    'ENABLED'                  => 'Enabled',
    'DISABLED'                 => 'Disabled',
    'SUPPORT'                  => 'Support',
    'NOT_SUPPORT'              => 'Not support',
    'OPEN_EXT'                 => 'Open {:extension} extension',
    'RAW_POST_DATA'            => ' closed detect',
    'CLOSE'                    => 'Close',
    'CLOSED'                   => 'Closed',
    'NOT_CLOSED'               => 'Not closed',
    'RAW_POST_SOLUTION'        => ' is not closed solution',
    'RAW_POST_SOLUTION1'       => 'Find the {:item} setting as follows:',
    'REWRITE_DETECT'           => 'Rewrite detection (enable rewrite is more conducive to website SEO optimization)',
    'SERVER'                   => 'Server',
    'DETECTING'                => 'Detecting',
    'SIZE_DETECT'              => 'Size limit detection',
    'UPLOAD_FILES'             => 'Updating files',
    'DIR_PERMISSION'           => 'Directory and file permissions check',
    'WRITE'                    => 'Write',
    'READ'                     => 'Read',
    'WRITABLE'                 => 'Writable',
    'NOT_WRITABLE'             => 'Not writable',
    'READABLE'                 => 'Readable',
    'NOT_READABLE'             => 'Unreadable',
    'TEST_AGAIN'               => 'Test again',
    'NEXT'                     => 'Next',
    'PREVIOUS'                 => 'Previous',
    'DATABASE_INFO'            => 'Database Information',
    'DATABASE_SERVER'          => 'Database server',
    'DATABASE_SERVER_TIP'      => 'Database server address, generally 127.0.0.1 or localhost.',
    'DATABASE_PORT'            => 'Database port',
    'DATABASE_PORT_TIP'        => 'Database server port, MySQL is generally 3306.',
    'DATABASE_USERNAME'        => 'Database username',
    'DATABASE_PASSWORD'        => 'Database password',
    'DATABASE_NAME'            => 'Database name',
    'DATABASE_NAME_TIP'        => 'Lowercase letters are best.',
    'TABLE_PREFIX'             => 'Table prefix',
    'TABLE_PREFIX_TIP'         => 'Default is recommended, need to be modified when multiple ThinkCMF are installed in the same database.',
    'DATABASE_CHARSET'         => 'Character set',
    'DATABASE_CHARSET_TIP'     => 'If your server is a virtual space that does not support uft8mb4, please select utf8.',
    'WEBSITE_CONFIG'           => 'Website configuration',
    'WEBSITE_NAME'             => 'Site name',
    'WEBSITE_NAME_TIP'         => 'ThinkCMF Content Management Framework',
    'WEBSITE_KEYWORD'          => 'SEO keywords',
    'WEBSITE_KEYWORD_TIP'      => 'ThinkCMF,php,CMF,cmf,cms,simplewind,framework',
    'SEO_DESCRIPTION'          => 'SEO description',
    'SEO_DESCRIPTION_TIP'      => 'ThinkCMF is a content management framework for rapid development released by Simplewind Network Technology.',
    'ADMIN_INFO'               => 'Administrator information',
    'ADMIN_ACCOUNT'            => 'Creator account',
    'ADMIN_ACCOUNT_TIP'        => 'Creator account, have all the management permissions of the site background',
    'PASSWORD'                 => 'Password',
    'RE_PASSWORD'              => 'Confirm Password',
    'PASSWORD_TIP'             => 'The password length should not be less than 6 digits and no more than 32 digits.',
    'DATABASE_LINK_FAILED'     => 'Database connection configuration failed',
    'URL_TIP'                  => 'Please end with "/"',
    'DB_HOST_ERROR'            => 'Database server address cannot be empty',
    'DB_PORT_ERROR'            => 'Database server port cannot be empty',
    'DB_USERNAME_ERROR'        => 'Database username cannot be empty',
    'DB_PASSWORD_ERROR'        => 'Database password cannot be empty',
    'DB_NAME_ERROR'            => 'Database name cannot be empty',
    'DB_PREFIX_ERROR'          => 'Database table prefix cannot be empty',
    'DB_ADMIN_ERROR'           => 'Administrator account cannot be empty',
    'DB_PASSWORD_ERROR'        => 'Password cannot be empty',
    'DB_PASSWORD_ERROR1'       => 'Password length cannot be less than {0} characters',
    'DB_PASSWORD_ERROR2'       => 'Password length cannot exceed {0} characters',
    'RE_PASSWORD_ERROR'        => 'Confirm password can not be empty',
    'RE_PASSWORD_ERROR1'       => 'The two passwords entered are inconsistent, please re-enter',
    'EMAIL_ERROR'              => 'Email can not be empty',
    'EMAIL_ERROR1'             => 'Please enter the correct email address',
    'INSTALLING'               => 'Installing',
    'CONGRATULATION'           => 'Congratulations, the installation is complete!',
    'FINISHED_TIP'             => $finished_tip,
    'FRONTEND'                 => 'Front end',
    'BACKEND'                  => 'Back end',
    'UPLOAD_PROHIBITED'        => 'Upload prohibited',
    'PASSWORD_6'               => 'Minimum password length is 6 characters',
    'PASSWORD_32'              => 'Maximum password length is 32 characters',
    'ILLEGAL_INSTALL'          => 'Illegal installation!',
    'INSTALL_FINISHED'         => 'Installation is complete!',
    'DB_CONFIG_WRITE_SUCCESS'  => 'Data configuration file is written successfully!',
    'DB_CONFIG_WRITE_FAILED'   => 'Failed to write data configuration file!',
    'SITE_CREATE_SUCCESS'      => 'The website is created!',
    'SITE_CREATE_FAILED'       => 'Website creation failed!',
    'TEMPLATE_NOT_EXIST'       => 'Template does not exist!',
    'TEMPLATE_INSTALL_SUCCESS' => 'The template is installed successfully!',
    'MENU_IMPORT_SUCCESS'      => 'Application background menu imported successfully!',
    'HOOK_IMPORT_SUCCESS'      => 'Application hook imported successfully!',
    'BEHAVIOR_IMPORT_SUCCESS'  => 'Successfully applied user behavior!',
    'DB_USER_PASS_ERROR'       => 'The database account or password is incorrect!',
    'VERIFY_SUCCESS'           => 'The verification is successful!',
    'INNODB_ERROR'             => 'The database account and password is verified, but InnoDb is not supported!',
    'REQUIRE_ILLEGAL'          => 'Illegal request method!',
    'SUCCESS'                  => ' successfully!',
    'FAILED'                   => ' failed!',
    'CREATE_DATA_TABLE'        => 'Create data table ',
    'SQL_SUCCESS'              => 'SQL executed successfully!',
    'SQL_FAILED'               => 'SQL executed failed!',
    'SITE_INFO_CONFIG_SUCCESS' => 'Website information configuration is successful!',
    'ADMIN_ACCOUNT_CREATED'    => 'The administrator account is created successfully!'
];
