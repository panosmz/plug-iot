# Plug-IoT

This project is an open-source solution for controlling IoT Smart Devices/Sensors, built as a university thesis at the [Department of Cultural Technology & Communication] (http://www.ct.aegean.gr/en/), [University of the Aegean] (https://www.aegean.gr/aegean2/index.html). The devices built for this project include smart plugs and on/off switches (for controlling lights, coffee makers etc.), temperature/humidity sensors and PIR motion sensors, all based on the ESP8266 WiFi module. A REST API is built for managing those devices and a web app is provided as the front-end.

![App screenshot](https://panosmazarakis.files.wordpress.com/2016/10/web-scr-2-1.jpg "Web App")


## Examples

Examples are hosted [here] (http://ct-iot.ct.aegean.gr/) (currently requires VPN connection to Aegean University) and [here] (http://83.212.118.5). Schematics for the devices are provided in [/esp8266-devices/schematics] (/esp8266-devices/schematics).

## Built With

* [Material Design Lite] (https://getmdl.io/) - The front-end template used
* [NodeMCU] (https://github.com/nodemcu/nodemcu-firmware) - Lua based firmware for ESP8266 WiFi SOC

### Prerequisites

For the web server: Apache 2, MySQL 5, PHP 7.

## Contributing

The code in this project is botched at some points, but it was build for demonstrative purposes and I also learned a lot in the process. So, any contribution is welcome.

## License

This project is licensed under the Apache 2.0 License - see the [LICENSE.md](LICENSE.md) file for details.