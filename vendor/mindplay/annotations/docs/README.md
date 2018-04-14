# PHPUnit-Mink Documentation

## Preparations

1. install [Sphinx](http://sphinx-doc.org/): `easy_install -U Sphinx`
2. install [Read the Docs Sphinx Theme](https://github.com/snide/sphinx_rtd_theme): `pip install sphinx_rtd_theme`
3. install [sphinx-autobuild](https://pypi.python.org/pypi/sphinx-autobuild/0.2.3): `pip install sphinx-autobuild`

## Automatic building

1. run `make livehtml` in the `docs` folder
2. open `http://localhost:8000/` to view the documentation

Thanks to the __sphinx-autobuild__ the documentation will be automatically built on every change and all browsers,
where it's opened will be reloaded automatically as well.

## One-time building

1. run `make html` in the `docs` folder
2. open `docs/build/html` folder in your browser
