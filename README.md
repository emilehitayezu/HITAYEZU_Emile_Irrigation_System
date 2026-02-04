# IoT Sensor Dashboard

A comprehensive web dashboard for monitoring IoT sensor data including soil moisture, humidity, temperature, and weather forecasts.

## Features

- **Secure Login System**: Username/password authentication
- **Real-time Monitoring**: Live sensor data updates every 3 seconds
- **Interactive Charts**: Real-time line charts and 24-hour historical bar charts
- **Weather Forecast**: 5-day weather prediction
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Modern UI**: Clean, professional interface with TailwindCSS

## Sensors Monitored

1. **Soil Moisture**: Percentage-based moisture levels
2. **Humidity**: Air humidity percentage
3. **Temperature**: Ambient temperature in Celsius
4. **Weather**: Current conditions and forecast

## Quick Start

1. Open `login.html` in your web browser
2. Use demo credentials:
   - Username: `admin`
   - Password: `admin123`
3. View the dashboard with live sensor data

## File Structure

```
├── login.html          # Login page with authentication
├── dashboard.html      # Main dashboard with sensor data
├── package.json        # Project configuration
└── README.md          # This file
```

## Technologies Used

- **HTML5**: Semantic markup
- **TailwindCSS**: Modern CSS framework
- **Chart.js**: Data visualization
- **JavaScript**: Interactivity and real-time updates
- **LocalStorage**: Session management

## Data Simulation

The dashboard simulates real sensor data with:
- Random value generation within realistic ranges
- 3-second update intervals
- Historical data for trend analysis
- Trend indicators (up/down from previous day)

## Customization

To connect to real IoT sensors:
1. Replace the simulated data functions with actual API calls
2. Update the authentication system to use your backend
3. Configure real-time data sources (WebSocket, MQTT, etc.)

## Security Notes

- This demo uses client-side authentication for demonstration only
- Production use requires server-side authentication
- Implement proper API security for real sensor data
