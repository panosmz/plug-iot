
--DATA---------------
data = {}
data.apiUrl = 'http://localhost/api/plug_device_api_ajax.php' --Replace with your API's URL
data.deviceName = 'devboard' --Replace with your device's name
data.deviceSerial = 'eeeeeeeeee' --Replace with thid device's unique serial number
data.slotActive = 0
data.serverExists = false;

file.open("data.json", "r")
jsonString = file.readline()
file.close()

jsonValues = {}
jsonValues = cjson.decode(jsonString)

slot = {}
for i=1,3 do
	local slotIndex = 'slot'..i
	slot[slotIndex] = {}
	slot[slotIndex]['ssid'] = jsonValues[slotIndex]['ssid']
	slot[slotIndex]['pass'] = jsonValues[slotIndex]['pass']
end
slot['key'] = jsonValues['key']

jsonString = nil
jsonValues = nil
collectgarbage()

led = 4
plug = 3
-----------------------------

---SET-PINS---------------------
gpio.mode(led, gpio.OUTPUT)
gpio.mode(plug, gpio.OUTPUT)
gpio.write(led, gpio.LOW)
gpio.write(plug, gpio.LOW)
-------------------------------
function connectNetworkBegin()
	data.slotActive = 0
	connectNetworkCheck(false)
end

function connectNetwork(ssid, pass, referer, key)
	wifi.setmode(wifi.STATION)
	wifi.sta.config(ssid, pass, 1)
	wifi.sta.connect()
	local joinCounter = 0
	local joinMaxAttempts = 10
	blink("slow")
	tmr.alarm(0, 3000, tmr.ALARM_AUTO, function()

		local ip = wifi.sta.getip()
		if ip == nil and joinCounter < joinMaxAttempts then

			joinCounter = joinCounter +1
		else
			local returnvalue
			if joinCounter == joinMaxAttempts then
				returnvalue = false;
			else
				returnvalue = true;
			end
			tmr.stop(0)
			joinCounter = nil
			joinMaxAttempts = nil
			collectgarbage()
			if(referer == 'newNetwork' and returnvalue) then
				blink("on")
				saveNewNetwork(ssid, pass, key)

				getToken()
				startUpdates()
			elseif(referer == 'newNetwork') then
				serverModeStart()
			else
				connectNetworkCheck(returnvalue)
			end
		end
	end)
end

function connectNetworkCheck(state)
	if (state == false and (data.slotActive < 3)) then
		data.slotActive = data.slotActive+1
		local slotIndex = 'slot'..data.slotActive
		if(slot[slotIndex]['ssid'] ~= "" and slot[slotIndex]['pass'] ~= "") then
			connectNetwork(slot[slotIndex]['ssid'], slot[slotIndex]['pass'], slot['key'])
		else
			connectNetworkCheck(false)
		end
	elseif (state == true) then
		blink("on")
		getToken()
		startUpdates()

	else

		serverModeStart()
	end
end


function checkConnection()
	connstatus = wifi.sta.status()

	if connstatus == 1 then
		tmr.stop(2)
		tmr.stop(3)
		connectNetwork()
	end
end


function blink(bl_speed)
	if(bl_speed == "off") then
		pwm.stop(led)
	elseif(bl_speed == "on") then
		pwm.setup(led, 1000, 1023)
		pwm.start(led)
	elseif(bl_speed == "data") then
		pwm.setup(led, 5, 500)
		pwm.start(led)
	elseif(bl_speed == "slow") then
		pwm.setup(led, 1, 500)
		pwm.start(led)
	elseif(bl_speed == "med") then
		pwm.setup(led, 2, 500)
		pwm.start(led)
	elseif(bl_speed == "fast") then
		pwm.setup(led, 10, 500)
		pwm.start(led)
	end
end

function getToken()

	tokenRequest = '{"action":"login","serial":"'..data.deviceSerial..'","key":"'..slot['key']..'"}'

	http.post(data.apiUrl,
	  'Content-Type: application/json\r\n',
	  tokenRequest,
	  function(code, response)
	    if (code < 0) then

	    else

	      data.jsonResponse = cjson.decode(response)
	      

	      if (data.jsonResponse['success'] == 'OK') then
	      	data.token = data.jsonResponse['token']

	      	blink('data')
	      	data.jsonPostUpdate = '{"action":"deviceGetStatus","serial":"'..data.deviceSerial..'","token":"'..data.token..'"}'
	      else

	      	blink('fast')
	      	node.restart()
	      end
	    end
	    blink("med")
	  end)
end

function getUpdate()

if ((wifi.sta.status() ~= 5) and not data.token) then
	tmr.stop(3)
	connectNetworkBegin()
else
	blink("on")
	http.post(data.apiUrl,
	  'Content-Type: application/json\r\n',
	  data.jsonPostUpdate,
	  function(code, response)
	    if (code < 0) then

	    else

	      data.jsonResponse = cjson.decode(response)
	      
	      if (data.jsonResponse['success'] == 'OK') then
	      	local currentState = gpio.read(plug)
	      	local pinUpdate = data.jsonResponse['status']

	      	if (currentState ~= pinUpdate) then
	      		if (pinUpdate == 0) then
	      			gpio.write(plug, gpio.LOW)
	      		else
	      			gpio.write(plug, gpio.HIGH)
	      		end
	      	end
	      end
	    end
	    blink("med")
	  end)
end
end

function startUpdates()
	tmr.alarm(3, 3000, tmr.ALARM_AUTO, function() getUpdate() end)
end

function serverModeStart()
	blink("fast")

	wifi.setmode(wifi.SOFTAP)
	cfg={}
	cfg.ssid="PLUG-"..data.deviceName
	wifi.ap.config(cfg)


	--start server
	if (not data.serverExists) then
		data.serverExists = true;
		srv=net.createServer(net.TCP)
	end
	srv:listen(80,function(conn)
  		conn:on("receive",function(conn,payload)

    		ssidL = {string.find(payload, "ssid=")}
    		passL = {string.find(payload, "pass=")}
    		keyL = {string.find(payload, "key=")}

    		if ((ssidL[2] ~= nil) and (passL[2] ~= nil) and (keyL[2] ~= nil)) then
    			data.PostedSSID = string.sub(payload,ssidL[2]+1,passL[1]-2)
    			data.PostedPASS = string.sub(payload,passL[2]+1,keyL[1]-2)
    			data.PostedKEY = string.sub(payload,keyL[2]+1,#payload)
    			data.PostedSSID = htmlChars(data.PostedSSID)
    			data.PostedPASS = htmlChars(data.PostedPASS)
    			data.PostedKEY = htmlChars(data.PostedKEY)
    			slot['key'] = data.PostedKEY
    			connectNetwork(data.PostedSSID, data.PostedPASS, 'newNetwork', data.PostedKEY)
    		end

    		conn:send('<!DOCTYPE HTML>\n'
				..'<html>\n'
				..'<head><meta  content="text/html; charset=utf-8">\n'
				..'<title>PLUG WIFI SETUP</title></head>\n'
				..'<body style="background-color: #d9d9d9;">\n'
				..'<div style="background-color: white; box-shadow: 1px 1px 5px 0px rgba(0,0,0,0.75);\n'
				..'width: 300px; margin: auto; padding: 30px; line-height: 25%; text-align: center;">\n'
				..'<h3 style="color: #00a2cc">Plug - Wi-Fi Setup</h5>\n'
				..'<p><b>Device:</b> '..data.deviceName..'</p>\n'
				..'<form  enctype="application/json" style="line-height: 200%" action="" method="POST">\n'
				..'<label>SSID </label><input type="text" name="ssid"><br>\n'
				..'<label>PASS </label><input type="text" name="pass"><br>\n'
				..'<label>Pairing Key </label><input type="text" name="key"><br>\n'
				..'<input style="background-color: #00a2cc; border:none; padding: 5px 20px; color:white;" type="submit" value="Connect">\n'
				..'</form>\n')
  		end)
  		conn:on("sent",function(conn) conn:close() end)
	end)
end

function serverModeUpdate()
	for mac,ip in pairs(wifi.ap.getclient()) do
	end
end

function saveNewNetwork(ssid, pass, key)
	slot['key'] = key
	if (slot['slot1']['ssid'] == "") then
		slot['slot1']['ssid'] = ssid
		slot['slot1']['pass'] = pass
	elseif (slot['slot2']['ssid'] == "") then
		slot['slot2']['ssid'] = ssid
		slot['slot2']['pass'] = pass
	else
		slot['slot3']['ssid'] = ssid
		slot['slot3']['pass'] = pass
	end
	blink("fast")
	if file.exists("dataBKP.json") then
		file.remove("dataBKP.json")
	end
	file.rename("data.json", "dataBKP.json")
	file.open("data.json", "w+")
	file.write(cjson.encode(slot))
	file.close()
	blink("on")
end

function htmlChars(strng)
	strng = string.gsub(strng, "%%20", " ")
	strng = string.gsub(strng, "%%21", "!")
	strng = string.gsub(strng, "%%40", "@")
	strng = string.gsub(strng, "%%23", "#")
	strng = string.gsub(strng, "%%24", "$")
	strng = string.gsub(strng, "%%25", "%%")
	strng = string.gsub(strng, "%%26", "&")
	strng = string.gsub(strng, "%%27", "'")
	strng = string.gsub(strng, "%%28", "(")
	strng = string.gsub(strng, "%%29", ")")
	strng = string.gsub(strng, "%%2D", "-")
	strng = string.gsub(strng, "+", " ")
	return strng
end

connectNetworkBegin()