# Composer build for the Drupal Umami installation profile and theme project

For more information including installation instructions visit [The Out of the Box Initiative issue on drupal.org](https://www.drupal.org/project/ideas/issues/2847582).

## Usage

```
lando start
lando drush si demo_umami --account-pass=pass --account-mail="your-email@example.com"
lando drush en demo_umami_content janus_ab -y
```

Edited to work with [Lando](https://docs.devwithlando.io/). Temporarily using this repo to clone between machines, not making a pull request :)
