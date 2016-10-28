# Flashing the ESP8266

First, flash the NodeMCU firmware to the devices with [nodemcu-flasher] (https://github.com/nodemcu/nodemcu-flasher). Then, I suggest using [ESPlorer] (http://esp8266.ru/esplorer/) to upload the code to the devices. After uploading the files, compile **main.lua** to **main.lc**.

## Required Firmware

Use [NodeMCU custom builds] (https://nodemcu-build.com/) to build the firmware for each device. Use the modules below:

For the on/off switch / motion sensor:

```
CJSON, file, GPIO, HTTP, net, node, PWM, timer, UART, WiFi
```

For the temperature/humidity sensor:

```
CJSON, DHT, file, GPIO, HTTP, net, node, PWM, timer, UART, WiFi
```