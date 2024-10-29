# Composer should-not

This plugin removes certain versions from your dependency list preventing them 
from being installed and provide a reason for doing so.

While the idea of versions constraint is that you can do this on your 
`composer.json` that works OK as long as everybody in your team knows what to do
when changing or requiring a new version constraint.

This module is meant to provide a fail-safe and document a reason for doing so 
that if others tries to change the current constraint that will fail and a 
warning with a reason will be provided.

> [!NOTE]
> [dev versions](https://getcomposer.org/doc/articles/versions.md#branches) are always allowed.

The configuration of this plugin is in the ["extra" property](https://getcomposer.org/doc/04-schema.md#extra)
of your `composer.json` file.

i.e.:

```json
{
    "extra": {
        "should-not": {
            "drupal/block_class": {
                "version": "^3",
                "reason": "Version constraint ^3 should not be installed, it is the a new release of the previous 1.x codebase."
            }
        }
    } 
}
```

The above is what encouraged the creation of this plugin, see https://www.drupal.org/project/block_class/issues/3468976.

They decided to create a new major version out of an older codebase :shrug:, 
and it has proven to be an issue for us, so with this we can preemptively avoid 
the module from being upgrade to 3.0.

However, other times I wanted something like this for ducumenting why a certain 
dependency should be locked to a specific version. Something that I needed 
several times in Drupal projects.

[![asciicast](https://asciinema.org/a/Vm7NWvuuFmP07l6wdHN5qaPZc.svg?v=1)](https://asciinema.org/a/Vm7NWvuuFmP07l6wdHN5qaPZc)
