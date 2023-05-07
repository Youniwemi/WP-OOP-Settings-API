<h1 align="center">
  WordPress Settings Kit - Lightweight library for easily creating WordPress Settings Pages and Post Metaboxes
</h1>


[![Tweet for help](https://img.shields.io/twitter/follow/rahalaboulfeth.svg?style=social&label=Tweet%20@rahalaboulfeth)](https://twitter.com/rahalaboulfeth/) [![GitHub stars](https://img.shields.io/github/stars/youniwemi/WP-OOP-Settings-API.svg?style=social&label=Stars)](https://github.com/youniwemi/WP-OOP-Settings-API/stargazers) [![GitHub followers](https://img.shields.io/github/followers/youniwemi.svg?style=social&label=Follow)](https://github.com/youniwemi?tab=followers) — :point_up: Make sure you :star: and :eyes: this repository!

> Ever wanted to build custom settings (or metaboxes) inside your WordPress plugin or theme and didn't like the non-DRY approach for creating custom settings and metaboxes via WordPress API? Well, this package is an attempt to fix this. 🎊

## Screenshots

![](https://i.imgur.com/EXUoeLZ.png)
![](https://i.imgur.com/sc9816W.png)


## COMPOSER INSTALL

* You can install the library using composer 
```bash
composer require youniwemi/wp-settings-kit

```

* You'll be able to use WP_Settings_Kit class after requiring vendor/autoload.php

## USAGE

### USAGE For Setting Page
* Prepare an array of options then instanciate WP_Settings_Kit
```php
$options = 
[
    'name' => 'MY_AWESOME_FEATURE',
    'title' => 'My Awesome Feature',
    'fields' => [
        [
            'id' => 'ACTIVE',
            'type' => 'checkbox',
            'title' => 'The feature is active' ,
        ],
        [
            'id' => 'FIRST_SETTING',
            'type' => 'number',
            'title' => 'First setting' ,
            'default' => 0 ,
            // This setting will be included only if the first checkbox is checked
            'show_if' => function(){ return defined('MY_AWESOME_FEATURE_ACTIVE') && MY_AWESOME_FEATURE_ACTIVE == 'on'; }
        ]
    ]
];
$setting = new WP_Settings_Kit($options);
```
* Once the options are saved, constants MY\_AWESOME\_FEATURE\_ACTIVE will be available and will be able to set the first setting MY\_AWESOME\_FEATURE\_FIRST\_SETTING 


### USAGE For Post Metabox
* Prepare an array of options as well as the metabox definition then instanciate WP_Settings_Kit

```php
$options = 
[
    'name' => 'MY_AWESOME_FEATURE',
    'title' => 'My Awesome Feature',
    'fields' => [
        [
            'id' => 'ACTIVE',
            'type' => 'checkbox',
            'title' => 'The feature is active' ,
        ],
        [
            'id' => 'FIRST_SETTING',
            'type' => 'number',
            'title' => 'First setting' ,
            'default' => 0 ,
            // This setting will be included only if the first checkbox is checked
            'show_if' => function(){ return defined('MY_AWESOME_FEATURE_ACTIVE') && MY_AWESOME_FEATURE_ACTIVE == 'on'; }
        ]
    ]
];
$metabox = [
    'id' => 'my_metabox',
    'title' => 'My Awesome Metabox',
    'post_types' => ['post'], // Post types to display meta box
    'context' => 'advanced',
    'priority' => 'default',
];
$metabox = new WP_Settings_Kit($options , $metabox);
```

* Once the metabox is saved, fields will be saved as post metas : MY\_AWESOME\_FEATURE\_FIRST\_ACTIVE  and   MY\_AWESOME\_FEATURE\_FIRST\_SETTING 



## TODO:
- [x] Basic Settings Page
- [x] Tabs on Settings Page with JS
- [x] Tabs on Settings Page with JS
- [x] Documentation for code workflow
- [x] Create Field: `text`
- [x] Create Field: `textarea`
- [x] Create Field: `url`
- [x] Create Field: `number`
- [x] Create Field: `checkbox`
- [x] Create Field: `multicheck`
- [x] Create Field: `radio`
- [x] Create Field: `select`
- [x] Create Field: `html`
- [x] Create Field: `wysiwyg`
- [x] Create Field: `file`
- [x] Create Field: `image`
- [x] Create Field: `password`
- [x] Create Field: `color`
- [x] Create Field: `email`
- [x] Create Field: `date`
- [x] Create Field (generated content with callback): `content`
- [x] Create Field: `range`
- [x] Support for post metabox
- [ ] Tutorials
- [ ] Blog post
- [ ] Documentation
- [ ] Re-factor the code with WP Standards
- [ ] Re-factor the code into classes

## License
Release under GNU GPL v2.0


## Credits

This package is a fork of [https://github.com/ahmadawais/WP-OOP-Settings-API](https://github.com/ahmadawais/WP-OOP-Settings-API)   based on the work of @tareq1988 [https://github.com/tareq1988/wordpress-settings-api-class](https://github.com/tareq1988/wordpress-settings-api-class) 

@AhmadAwais, @deviorobert, @MaedahBatool
AND @WordPress, @tareq1988, @royboy789, @twigpress, @rahal.


---
### 🙌 [Youniwemi](https://www.youniwemi.com)

This open source fork is maintained by the help of awesome businesses listed below :
- [Youniwemi](https://www.youniwemi.com)
- [Instareza](https://www.instareza.com)


This package is used in the following wordpress plugins
- [Mail Control](https://wordpress.org/plugins/mail-control/)
- [Aiify Blocks](https://wordpress.org/plugins/aiify/)

<br />
<br />
<p align="center">
<strong>For anything else, tweet at <a href="https://twitter.com/rahalaboulfeth/" target="_blank" rel="noopener noreferrer">@rahalaboulfeth</a></strong>
</p>
