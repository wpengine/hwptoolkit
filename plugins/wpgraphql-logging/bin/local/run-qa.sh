#!/usr/bin/env bash

# Running PHP Code Quality Analysis locally
composer run check-cs
composer run phpstan
composer run php:psalm
