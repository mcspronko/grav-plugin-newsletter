# Notice
This plugin is under development. Pull requests and contributions are more than welcome.

# Newsletter Plugin for Grav CMS
Newsletter Plugin enables users fill in subscription form on a website based on Grav CMS.

## Installation

### Development

Use modman package manager to install the plugin.

From the Grav CMS project directory perform the commands
```bash
modman init
modman clone git@github.com:mcspronko/grav-plugin-newsletter.git
```

As a result, the new newsletter plugin should appear under user/plugins/newsletter directory. 

## Example of Form Configuration
```yaml
form:
    name: newsletter
    action: /subscribe.json
    fields:
        -
            name: email
            size: field-size
            outerclasses: field-position
            placeholder: 'Your Email Here'
            type: email
            validate:
                required: true
    buttons:
        -
            type: submit
            classes: 'button submit'
            value: Subscribe
    process:
        -
            subscribe:
                fileprefix: subscriber-
                dateformat: Ymd-His-u
                extension: txt
                body: '{% include ''forms/data.txt.twig'' %}'
        -
            message: 'Thank you for your subscription!'
```
