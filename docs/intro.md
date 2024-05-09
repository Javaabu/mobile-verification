---
title: Introduction
sidebar_position: 1.0
---

# Mobile Verification

[Mobile Verification](https://github.com/Javaabu/mobile-verification) is a frontend agnostic authentication backend implementation for Laravel using mobile number verification. It provides a set of features to enable mobile number verification in your Laravel application.

The package itself does not register any routes, views, or controllers. Instead, it provides a set of traits and base controllers that can be extended for different use cases.

Since the package is frontend-agnostic, it is meant to be paired with your own routes, user interface, and controllers. We will discuss exactly how to create routes and controllers in the remainder of this documentation.

## When Should I Use This Package?
You may be wondering when it is appropriate to use this package. This package implements authentication using mobile number verification. If you are looking to implement mobile number verification in your Laravel application, this package is for you. You may use this package to authenticate one or more types of users. This does not restrict you from using other authentication types for other types of users.

