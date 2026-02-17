# ACF Unique ID Field

An [Advanced Custom Fields](https://www.advancedcustomfields.com/) field which generates a unique ID value.

While this library was originally developer for use in repeaters where each field in a repeater block needs to be given a persistent unique ID, it can be used anywhere an automatically-generated unique ID is required.

## Installation

```
composer require philipnewcomer/acf-unique-id-field
```

## Usage

The field type initializes automatically when this package is loaded.

Select the "Unique ID" field type when using the ACF GUI.

When editing a post, unique IDs will be generated on the initial save.

## Notes

IDs are generated as UUID v4 values (via `wp_generate_uuid4()` when available), for example:

```
86014fa3-9509-4a37-9817-8f9ec1f95f6f
```
