# File upload helper
File upload helper is the full documented (for now in Russian) for uploading a file via HTTP POST request.
Example:
~~~
$file = '/home/var/test.zip';
$response = (new FileUploadHelper())
    ->setBootstrapUrl('http://localhost/')
    ->sendPostContent('&filename=' . basename($file), array(), array($file))
    ->getResponse()
;
~~~

## Installation
Add the dependency directly to your `composer.json` file:
```
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/zapalm/fileUploadHelper"
  }
],
"require": {
  "php": ">=5.2",
  "zapalm/fileUploadHelper": "dev-master"
},
```

## How to help the project grow and get updates
* **Become the [patron][2]** to help me work more for supporting and improving this project.
* Report an issue.
* Give me feedback or [contact with me][3].
* Give the star to the project.
* Contribute to the code.

## Contributing to the code

### Requirements for code contributors

Contributors **must** follow the following rules:

* **Make your Pull Request on the *dev* branch**, NOT the *master* branch.
* Do not update a helper version number.
* Follow [PSR coding standards][1].

### Process in details for code contributors

Contributors wishing to edit the project's files should follow the following process:

1. Create your GitHub account, if you do not have one already.
2. Fork the project to your GitHub account.
3. Clone your fork to your local machine.
4. Create a branch in your local clone of the project for your changes.
5. Change the files in your branch. Be sure to follow [the coding standards][1].
6. Push your changed branch to your fork in your GitHub account.
7. Create a pull request for your changes **on the *dev* branch** of the project.
   If you need help to make a pull request, read the [Github help page about creating pull requests][4].
8. Wait for the maintainer to apply your changes.

**Do not hesitate to create a pull request if even it's hard for you to apply the coding standards.**

[1]: https://www.php-fig.org/psr/
[2]: https://www.patreon.com/zapalm
[3]: http://zapalm.ru/
[4]: https://help.github.com/articles/about-pull-requests/