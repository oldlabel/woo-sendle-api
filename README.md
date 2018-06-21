# Woo Sendle API
A plugin for WooCommerce that enables shop owners to book packages on Sendle and retrieve packing labels without leaving WooCommerce.

## Getting Started

### Prerequisites
To use Woo Sendle API you need to have:
* WordPress with WooCommerce plugin activated
* A Sendle account with an API username and password

###### WooCommerce
The plugin has been tested with WooCommerce versions 3.2.6 to 3.4.2. This plugin is likely to work with all releases of WooCommerce under 3.4.

**Note** _Actions_ must be enabled under _Screen Options_ in WooCommerce v3.4 to show Woo Sendle API actions.

###### Sendle
Follow the steps in this [Sendle API support article](https://support.sendle.com/hc/en-us/articles/210798518-Sendle-API) to gain access to your Sendle API key.

### Installing
Copy the files into your WordPress plugins folder and activate via the WordPress _plugins_ page.

#### Configuration
The plugin is configured in an extra tab called _Sendle API_ in the standard WooCommerce Settings section. The settings page lets you configure:
* Enable/disable the plugin
* Connection mode (sandbox or live)
* API username and key
* Default pickup information

### Usage
The plugin is used by an extra button in the _Actions_ section of the WooCommerce orders page. The booking button is only available for orders in a status of __**processing**__. If order has been booked then the button will always be available.

FAQ to be completed.

**Note** The plugin can only create one booking per order. To create a second booking you will need to login to the Sendle website and book directly via the dashboard.

### License
The project is licensed under GNU v3 - see LICENSE file for details.

### Acknowledgments
Thanks to all those who contribute answers to questions on various websites, whose code was used for inspiration and often simplicity.

### Issues and Future Enhancements
Please make contact if you find any issues.

#### Future Enhancements
A number of enhancements are already considered and will be implmented in time. The current list:
1. Optimised and simplified code
2. See 1.
3. Calculate weight based on item weights
3. Back-end cron task to keep Sendle booking statuses updated automatically
4. Dashboard graphic for mean delivery time
5. Quote prices to front-end users
6. Optimise code again
