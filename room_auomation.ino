#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <ArduinoJson.h>

// WiFi credentials
const char* ssid = "MVGR_SLC";
const char* password = "001010011100";

// PHP Server URLs
const char* serverUrlStatus = "http://192.168.16.111/room/device_status.php";  // Existing URL for manual control
const char* serverUrlCount = "http://192.168.16.111/room/getcount.php?sensor1=count";  // New URL to get the count

// GPIO pins for fans and lights
const int fan1Pin = D1;
const int fan2Pin = D2;
const int fan3Pin = D3;
const int fan4Pin = D4;
const int light1Pin = D5;
const int light2Pin = D6;
const int light3Pin = D7;

// Store the previous states
String prevState[7] = {"OFF", "OFF", "OFF", "OFF", "OFF", "OFF", "OFF"};

// Variable to track previous count value
int prevCount = -1;

void setup() {
  Serial.begin(9600);

  // Set pins as outputs
  pinMode(fan1Pin, OUTPUT);
  pinMode(fan2Pin, OUTPUT);
  pinMode(fan3Pin, OUTPUT);
  pinMode(fan4Pin, OUTPUT);
  pinMode(light1Pin, OUTPUT);
  pinMode(light2Pin, OUTPUT);
  pinMode(light3Pin, OUTPUT);

  // Default all fans and lights to OFF (HIGH for off state)
  digitalWrite(fan1Pin, HIGH);
  digitalWrite(fan2Pin, HIGH);
  digitalWrite(fan3Pin, HIGH);
  digitalWrite(fan4Pin, HIGH);
  digitalWrite(light1Pin, HIGH);
  digitalWrite(light2Pin, HIGH);
  digitalWrite(light3Pin, HIGH);

  // Initialize WiFi connection
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi...");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("Connected!");
}

void loop() {
  if (WiFi.status() == WL_CONNECTED) {
    // Send request to get the current count value from the server
    int countValue = getCountFromServer();
    
    // Only proceed if we received a valid count value
    if (countValue == 0) {
      Serial.print("Current count value: ");
      Serial.println(countValue);
      digitalWrite(fan1Pin, HIGH);
      digitalWrite(fan2Pin, HIGH);
      digitalWrite(fan3Pin, HIGH);
      digitalWrite(fan4Pin, HIGH);
      digitalWrite(light1Pin, HIGH);
      digitalWrite(light2Pin, HIGH);
      digitalWrite(light3Pin, HIGH);
      Serial.println("All devices OFF (count = 0)");
    }
   else if(countValue > 0){
    // Uncomment to handle manual control if needed
    handleManualControl();}

  } else {
    // If not connected to WiFi, turn off all devices
    Serial.println("WiFi not connected. Reconnecting...");
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
      delay(1000);
      Serial.print(".");
    }
    Serial.println("Reconnected!");
  }

  delay(500);  // Wait for 500 milliseconds before the next loop
}

// Function to fetch the count value from the PHP server
int getCountFromServer() {
  HTTPClient http;
  WiFiClient client;

  // Send GET request to the count URL
  http.begin(client, serverUrlCount);
  int httpCode = http.GET();

  if (httpCode > 0) {
    Serial.print("Count GET response code: ");
    Serial.println(httpCode);

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Count received: " + payload);
      int count = payload.toInt();
      http.end();
      return count;
    }
  }

  http.end();
  return -1;  // Return -1 if there was an error
}

// Function to handle manual control of fans and lights via server response
void handleManualControl() {
  HTTPClient http;
  WiFiClient client;

  // Send GET request to the manual control URL
  String url = String(serverUrlStatus) + "?status=online";
  http.begin(client, url);
  int httpCode = http.GET();

  if (httpCode > 0) {
    Serial.print("Manual control GET response code: ");
    Serial.println(httpCode);

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      Serial.println("Received manual control data: " + payload);

      // Process the JSON response
      DynamicJsonDocument doc(1024);
      DeserializationError error = deserializeJson(doc, payload);

      if (error) {
        Serial.print(F("deserializeJson() failed: "));
        Serial.println(error.c_str());
        return;
      }

      bool statusChanged = false;
      JsonArray devices = doc["devices"].as<JsonArray>();

      for (JsonObject device : devices) {
        const char* deviceName = device["device_name"];
        const char* state = device["state"];

        Serial.print("Device: ");
        Serial.print(deviceName);
        Serial.print(" State: ");
        Serial.println(state);

        if (strcmp(deviceName, "Fan1") == 0) {
          digitalWrite(fan1Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[0] != String(state)) {
            statusChanged = true;
            prevState[0] = String(state);
          }
        } else if (strcmp(deviceName, "Fan2") == 0) {
          digitalWrite(fan2Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[1] != String(state)) {
            statusChanged = true;
            prevState[1] = String(state);
          }
        } else if (strcmp(deviceName, "Fan3") == 0) {
          digitalWrite(fan3Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[2] != String(state)) {
            statusChanged = true;
            prevState[2] = String(state);
          }
        } else if (strcmp(deviceName, "Fan4") == 0) {
          digitalWrite(fan4Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[3] != String(state)) {
            statusChanged = true;
            prevState[3] = String(state);
          }
        } else if (strcmp(deviceName, "Light1") == 0) {
          digitalWrite(light1Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[4] != String(state)) {
            statusChanged = true;
            prevState[4] = String(state);
          }
        } else if (strcmp(deviceName, "Light2") == 0) {
          digitalWrite(light2Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[5] != String(state)) {
            statusChanged = true;
            prevState[5] = String(state);
          }
        } else if (strcmp(deviceName, "Light3") == 0) {
          digitalWrite(light3Pin, strcmp(state, "ON") == 0 ? LOW : HIGH);
          if (prevState[6] != String(state)) {
            statusChanged = true;
            prevState[6] = String(state);
          }
        }
      }

      if (statusChanged) {
        Serial.println("Device states changed based on manual control.");
      }
    }
  }

  http.end();
}