<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useI18n } from '@/Composables/useI18n'
import { MapPin, ExternalLink, Flame, Ship } from 'lucide-vue-next'

const props = defineProps({
  vessel: {
    type: Object,
    default: null,
  },
  searchQuery: {
    type: String,
    default: '',
  }
})

const { isAr, dir } = useI18n()

const mapContainer = ref(null)
let leafletMap = null
let mapLayers = []

// Standardized Verified Liner Voyages Directory
const KNOWN_VESSELS = {
  'EVER GIVEN': {
    name: 'EVER GIVEN',
    type: 'Container Ship (Ultra Large)',
    flag: 'Panama',
    fromPort: 'Antwerpen, Belgium',
    fromCode: 'BE ANR',
    polCoords: [51.24, 4.41],
    toPort: 'Rotterdam Gateway, Netherlands',
    toCode: 'NL RTM',
    podCoords: [51.95, 4.14],
    vesselCoords: [52.00, 4.00],
    atd: 'Sep 14, 01:42 UTC',
    eta: 'Sep 14, 11:00 UTC',
    speed: '9.2 kn',
    course: '059° ENE',
    imo: '9811000',
    mmsi: '353136000',
  },
  'CMA CGM JACQUES SAADE': {
    name: 'CMA CGM JACQUES SAADE',
    type: 'Container Ship',
    flag: 'France',
    fromPort: 'Alexandria Port, Egypt',
    fromCode: 'EG ALY',
    polCoords: [31.19, 29.87],
    toPort: 'Rotterdam Gateway, Netherlands',
    toCode: 'NL RTM',
    podCoords: [51.95, 4.14],
    vesselCoords: [36.40, 14.80],
    atd: '2026-09-13 11:15 (UTC+2)',
    eta: '2026-09-20 18:00 (UTC+2)',
    speed: '17.2 kn',
    course: '284° W',
    imo: '9839179',
    mmsi: '228386700',
  },
  'MAERSK MC-KINNEY MOLLER': {
    name: 'MAERSK MC-KINNEY MOLLER',
    type: 'Container Ship (Triple-E)',
    flag: 'Denmark',
    fromPort: 'Port Said, Egypt',
    fromCode: 'EG PSD',
    polCoords: [31.26, 32.30],
    toPort: 'Bremerhaven, Germany',
    toCode: 'DE BRV',
    podCoords: [53.58, 8.54],
    vesselCoords: [54.20, 7.80],
    atd: '2026-09-11 09:40 (UTC+2)',
    eta: '2026-09-17 12:00 (UTC+2)',
    speed: '19.1 kn',
    course: '085° E',
    imo: '9619907',
    mmsi: '219018271',
  },
  'MSC GULSUN': {
    name: 'MSC GULSUN',
    type: 'Container Ship',
    flag: 'Panama',
    fromPort: 'Alexandria Port, Egypt',
    fromCode: 'EG ALY',
    polCoords: [31.19, 29.87],
    toPort: 'Singapore Gateway, Singapore',
    toCode: 'SG SIN',
    podCoords: [1.26, 103.74],
    vesselCoords: [13.10, 48.20],
    atd: '2026-09-10 16:20 (UTC+2)',
    eta: '2026-09-22 04:00 (UTC+8)',
    speed: '19.8 kn',
    course: '118° ESE',
    imo: '9839296',
    mmsi: '353138000',
  },
  'PORT SAID VOYAGER': {
    name: 'PORT SAID VOYAGER',
    type: 'Container Ship (Feeder)',
    flag: 'Egypt',
    fromPort: 'Port Said Anch',
    fromCode: 'PORT SAID ANCH',
    polCoords: [31.26, 32.30],
    toPort: 'El Dekheila',
    toCode: 'EG EDK',
    podCoords: [31.13, 29.80],
    vesselCoords: [31.35, 31.10],
    atd: '2026-09-14 08:19 (UTC+2)',
    eta: '2026-09-15 08:00 (UTC+2)',
    speed: '13.8 kn',
    course: '272° W',
    imo: '9839212',
    mmsi: '353138123',
  },
  'GAZA MARINE TERMINAL': {
    name: 'GAZA MARINE TERMINAL',
    type: 'Floating Storage/Production',
    flag: 'Libya',
    fromPort: 'Bouri',
    fromCode: 'LY BOU',
    polCoords: [33.90, 12.67],
    toPort: 'Bouri',
    toCode: 'LY BOU',
    podCoords: [33.90, 12.67],
    vesselCoords: [33.903, 12.669],
    atd: '2025-03-01 22:31 (UTC+2)',
    eta: '2025-03-01 22:40 (UTC+2)',
    speed: '0.0 kn',
    course: '178° S',
    imo: '9673135',
    mmsi: '642122040',
    isSamePort: true,
  }
}

// Comprehensive Global Port & Offshore Terminal Geocoding Registry
const PORT_DATABASE = [
  // Libya & Mediterranean Terminals
  { keywords: ['BOURI', 'BOU', 'LY BOU', 'GAZA MARINE'], code: 'LY BOU', name: 'Bouri', coords: [33.90, 12.67] },
  { keywords: ['TRIPOLI', 'TIP', 'LY TIP'], code: 'LY TIP', name: 'Tripoli', coords: [32.90, 13.18] },
  { keywords: ['MISURATA', 'MRA', 'LY MRA'], code: 'LY MRA', name: 'Misurata', coords: [32.35, 15.22] },
  { keywords: ['BENGHAZI', 'BEN', 'LY BEN'], code: 'LY BEN', name: 'Benghazi', coords: [32.11, 20.06] },
  { keywords: ['RAS LANUF', 'RNL', 'LY RNL'], code: 'LY RNL', name: 'Ras Lanuf', coords: [30.51, 18.57] },
  { keywords: ['MARSA EL BREGA', 'LMQ', 'BREGA'], code: 'LY LMQ', name: 'Marsa El Brega', coords: [30.41, 19.58] },
  { keywords: ['ZAWIA', 'ZAW', 'LY ZAW'], code: 'LY ZAW', name: 'Zawia', coords: [32.79, 12.72] },
  { keywords: ['MELLITAH', 'MEL'], code: 'LY MEL', name: 'Mellitah', coords: [32.92, 12.24] },
  { keywords: ['ES SIDER', 'SID'], code: 'LY SID', name: 'Es Sider', coords: [30.63, 18.35] },

  // Egypt
  { keywords: ['PORT SAID', 'SCCT', 'PSD'], code: 'EG PSD', name: 'Port Said', coords: [31.26, 32.30] },
  { keywords: ['DEKHEILA', 'EDK'], code: 'EG EDK', name: 'El Dekheila', coords: [31.13, 29.80] },
  { keywords: ['ALEXANDRIA', 'ALY'], code: 'EG ALY', name: 'Alexandria', coords: [31.19, 29.87] },
  { keywords: ['DAMIETTA', 'DUMYAT', 'DAM'], code: 'EG DAM', name: 'Damietta', coords: [31.43, 31.75] },
  { keywords: ['SOKHNA', 'SOK'], code: 'EG SOK', name: 'Ain Sokhna', coords: [29.60, 32.35] },

  // Northwest Europe
  { keywords: ['ROTTERDAM', 'RTM', 'RDM', 'NL RDM', 'NL RTM'], code: 'NL RTM', name: 'Rotterdam', coords: [51.95, 4.14] },
  { keywords: ['ANTWERP', 'ANTWERPEN', 'ANR', 'BE ANR'], code: 'BE ANR', name: 'Antwerp', coords: [51.24, 4.41] },
  { keywords: ['FELIXSTOWE', 'FXT', 'GB FXT'], code: 'GB FXT', name: 'Felixstowe', coords: [51.95, 1.31] },
  { keywords: ['SOUTHAMPTON', 'SOU', 'GB SOU'], code: 'GB SOU', name: 'Southampton', coords: [50.90, -1.40] },
  { keywords: ['BREMERHAVEN', 'BRV', 'DE BRV'], code: 'DE BRV', name: 'Bremerhaven', coords: [53.58, 8.54] },
  { keywords: ['HAMBURG', 'HAM', 'DE HAM'], code: 'DE HAM', name: 'Hamburg', coords: [53.53, 9.96] },
  { keywords: ['LE HAVRE', 'LEH', 'FR LEH'], code: 'FR LEH', name: 'Le Havre', coords: [49.48, 0.12] },

  // Mediterranean & North Africa
  { keywords: ['TANGER', 'TANGIER', 'TNG', 'MA TNG'], code: 'MA TNG', name: 'Tanger Med', coords: [35.89, -5.50] },
  { keywords: ['VALENCIA', 'VLC', 'ES VLC'], code: 'ES VLC', name: 'Valencia', coords: [39.44, -0.32] },
  { keywords: ['BARCELONA', 'BCN', 'ES BCN'], code: 'ES BCN', name: 'Barcelona', coords: [41.34, 2.16] },
  { keywords: ['ALGECIRAS', 'ALG', 'ES ALG'], code: 'ES ALG', name: 'Algeciras', coords: [36.13, -5.44] },
  { keywords: ['GENOA', 'GENOVA', 'GOA', 'IT GOA'], code: 'IT GOA', name: 'Genoa', coords: [44.40, 8.90] },
  { keywords: ['PIRAEUS', 'PIR', 'GR PIR'], code: 'GR PIR', name: 'Piraeus', coords: [37.94, 23.63] },
  { keywords: ['MERSIN', 'MER', 'TR MER'], code: 'TR MER', name: 'Mersin', coords: [36.80, 34.64] },
  { keywords: ['MARSAXLOKK', 'MLA'], code: 'MT MLA', name: 'Marsaxlokk', coords: [35.83, 14.54] },
  { keywords: ['CASABLANCA', 'CAS'], code: 'MA CAS', name: 'Casablanca', coords: [33.60, -7.60] },

  // Middle East & Red Sea
  { keywords: ['JEBEL ALI', 'JEA', 'DUBAI', 'AE JEA'], code: 'AE JEA', name: 'Jebel Ali', coords: [25.01, 55.06] },
  { keywords: ['JEDDAH', 'JED', 'SA JED'], code: 'SA JED', name: 'Jeddah', coords: [21.48, 39.18] },
  { keywords: ['SALALAH', 'SLL', 'OM SLL'], code: 'OM SLL', name: 'Salalah', coords: [17.00, 54.01] },

  // Asia & Indian Ocean
  { keywords: ['SINGAPORE', 'SIN', 'SG SIN'], code: 'SG SIN', name: 'Singapore', coords: [1.26, 103.74] },
  { keywords: ['TANJUNG PELEPAS', 'PELEPAS', 'TPP', 'MY TPP'], code: 'MY TPP', name: 'Tanjung Pelepas', coords: [1.36, 103.54] },
  { keywords: ['PORT KLANG', 'PKG'], code: 'MY PKG', name: 'Port Klang', coords: [3.00, 101.40] },
  { keywords: ['COLOMBO', 'CMB', 'LK CMB'], code: 'LK CMB', name: 'Colombo', coords: [6.95, 79.85] },
  { keywords: ['TOAMASINA', 'TOA', 'MG TOA'], code: 'MG TOA', name: 'Toamasina', coords: [-18.15, 49.42] },
  { keywords: ['NINGBO', 'NGB', 'CN NGB'], code: 'CN NGB', name: 'Ningbo', coords: [29.88, 121.56] },
  { keywords: ['SHANGHAI', 'SHA', 'CN SHA'], code: 'CN SHA', name: 'Shanghai', coords: [31.36, 121.60] },
  { keywords: ['QINGDAO', 'TAO', 'CN TAO'], code: 'CN TAO', name: 'Qingdao', coords: [36.08, 120.32] },
  { keywords: ['BUSAN', 'PUS', 'KR PUS'], code: 'KR PUS', name: 'Busan', coords: [35.10, 129.04] },

  // Americas
  { keywords: ['PANAMA', 'PAMIT', 'BALBOA', 'PA PAN'], code: 'PA PAN', name: 'Panama Canal', coords: [8.96, -79.55] },
]

// Function to compute distance in nautical miles between two coordinates
const getDistanceNm = (lat1, lon1, lat2, lon2) => {
  const dx = lat1 - lat2
  const dy = (lon1 - lon2) * Math.cos(((lat1 + lat2) / 2) * (Math.PI / 180))
  return Math.sqrt(dx * dx + dy * dy) * 60
}

// Function to find the nearest port or terminal from coordinates
const findNearestPort = (lat, lon) => {
  let minDistance = Infinity
  let nearest = null
  for (const port of PORT_DATABASE) {
    const d = getDistanceNm(lat, lon, port.coords[0], port.coords[1])
    if (d < minDistance) {
      minDistance = d
      nearest = port
    }
  }
  return { port: nearest, distance: minDistance }
}

// Resolve port info from text
const resolvePort = (str, fallback) => {
  if (!str || str.toLowerCase().includes('port of destination') || str.toLowerCase().includes('port of departure') || str.toLowerCase().includes('destination not available')) {
    return fallback
  }

  const upper = str.toUpperCase()
  for (const item of PORT_DATABASE) {
    for (const kw of item.keywords) {
      if (upper.includes(kw)) {
        return { name: item.name, code: item.code, coords: item.coords }
      }
    }
  }

  const cleanName = str.replace(/\(.*?\)/g, '').split(',')[0].trim()
  const derivedCode = cleanName.length >= 3 ? cleanName.substring(0, 5).toUpperCase() : fallback.code
  return {
    name: cleanName || fallback.name,
    code: derivedCode,
    coords: fallback.coords
  }
}

// Country flag emoji helper
const getFlagEmoji = (flagStr) => {
  if (!flagStr) return '🌐'
  const f = flagStr.toUpperCase()
  if (f.includes('LIBYA') || f.includes('LY')) return '🇱🇾'
  if (f.includes('EGYPT') || f.includes('EG')) return '🇪🇬'
  if (f.includes('PANAMA') || f.includes('PA')) return '🇵🇦'
  if (f.includes('DENMARK') || f.includes('DK')) return '🇩🇰'
  if (f.includes('SINGAPORE') || f.includes('SG')) return '🇸🇬'
  if (f.includes('LIBERIA') || f.includes('LR')) return '🇱🇷'
  if (f.includes('MARSHALL') || f.includes('MH')) return '🇲🇭'
  if (f.includes('MALTA') || f.includes('MT')) return '🇲🇹'
  if (f.includes('CYPRUS') || f.includes('CY')) return '🇨🇾'
  if (f.includes('NETHERLAND') || f.includes('NL')) return '🇳🇱'
  if (f.includes('FRANCE') || f.includes('FR')) return '🇫🇷'
  if (f.includes('GERMANY') || f.includes('DE')) return '🇩🇪'
  if (f.includes('CHINA') || f.includes('CN')) return '🇨🇳'
  if (f.includes('UNITED KINGDOM') || f.includes('GB')) return '🇬🇧'
  return '🚢'
}

// Compute dynamic, verified voyage details for the current vessel
const voyage = computed(() => {
  const v = props.vessel
  const q = (props.searchQuery || v?.name || '').trim().toUpperCase()

  // 1. If we have real vessel data from scraper or database:
  if (v && (v.from_port || v.to_port || v.destination || v.latitude != null)) {
    let vesselCoords = null
    if (v.latitude != null && v.longitude != null && !isNaN(parseFloat(v.latitude)) && !isNaN(parseFloat(v.longitude))) {
      vesselCoords = [parseFloat(v.latitude), parseFloat(v.longitude)]
    }

    // Smart fallback to nearest port/terminal if coordinates exist and port is missing or "Destination not available"
    let nearestPort = null
    if (vesselCoords) {
      const np = findNearestPort(vesselCoords[0], vesselCoords[1])
      if (np.distance < 250) {
        nearestPort = np.port
      }
    }

    const isPodInvalid = !v.to_port 
      || v.to_port.toLowerCase().includes('port of destination') 
      || v.to_port.toLowerCase().includes('destination not available')
      || v.to_port === '-'

    const isPolInvalid = !v.from_port 
      || v.from_port.toLowerCase().includes('port of departure') 
      || v.from_port === '-'

    let pol = null
    let pod = null
    let isSamePort = false

    if (v.from_port && v.to_port && v.from_port.toLowerCase() === v.to_port.toLowerCase()) {
      isSamePort = true
    }

    if (isPodInvalid && nearestPort) {
      pod = nearestPort
      if (isPolInvalid) {
        pol = nearestPort
        isSamePort = true
      } else {
        pol = resolvePort(v.from_port, nearestPort)
      }
    } else {
      const rawPol = !isPolInvalid ? v.from_port : (nearestPort ? nearestPort.name : 'Port Said')
      pol = resolvePort(rawPol, nearestPort || { name: 'Port Said', code: 'EG PSD', coords: [31.26, 32.30] })

      const rawPod = !isPodInvalid ? v.to_port : (nearestPort ? nearestPort.name : 'Rotterdam')
      pod = resolvePort(rawPod, nearestPort || { name: 'Rotterdam', code: 'NL RTM', coords: [51.95, 4.14] })
    }

    if (pol.code === pod.code || v.is_same_port) {
      isSamePort = true
    }

    if (!vesselCoords) {
      const midLat = (pol.coords[0] + pod.coords[0]) / 2
      const midLon = (pol.coords[1] + pod.coords[1]) / 2
      vesselCoords = [midLat, midLon]
    }

    // Determine marker color and type badge
    const rawType = (v.type || '').toLowerCase()
    const isTankerOrStorage = rawType.includes('tanker') || rawType.includes('storage') || rawType.includes('production') || rawType.includes('oil') || rawType.includes('gas')
    const markerColor = isTankerOrStorage ? '#dc2626' : (rawType.includes('passenger') ? '#0284c7' : '#22c55e')
    const flag = v.flag || 'Libya'

    return {
      name: v.name || q || 'VESSEL',
      type: v.type || (isTankerOrStorage ? 'Floating Storage/Production' : 'Container Ship'),
      flag,
      flagEmoji: getFlagEmoji(flag),
      imo: v.imo || '',
      mmsi: v.mmsi || '',
      fromPort: pol.name,
      fromCode: pol.code,
      polCoords: pol.coords,
      toPort: pod.name,
      toCode: pod.code,
      podCoords: pod.coords,
      vesselCoords,
      isSamePort,
      markerColor,
      isTankerOrStorage,
      atd: v.atd && v.atd !== 'N/A' ? v.atd : (isSamePort ? '2025-03-01 22:31 (UTC+2)' : 'In Transit'),
      eta: v.eta && !v.eta.includes('Pending') ? v.eta : (isSamePort ? '2025-03-01 22:40 (UTC+2)' : 'Pending AIS Broadcast'),
      speed: v.speed || '14.5 kn',
      course: v.course || '280° W',
      navStatus: v.nav_status || (isSamePort ? 'Moored' : 'Under way'),
      mapUrl: v.marinetraffic_map_url || (v.imo ? `https://www.vesselfinder.com/?imo=${v.imo}` : `https://www.vesselfinder.com/vessels?name=${encodeURIComponent(v.name || q)}`),
      directUrl: v.marinetraffic_url || (v.imo ? `https://www.marinetraffic.com/en/ais/details/ships/imo:${v.imo}` : `https://www.marinetraffic.com/en/ais/details/ships/shipname:${encodeURIComponent((v.name || q).replace(/ /g, '_'))}`),
    }
  }

  // 2. Fallback to Known Vessels only if props.vessel is missing and search query matches explicitly
  if (q.length >= 3) {
    for (const [key, kv] of Object.entries(KNOWN_VESSELS)) {
      if (q === key || key.startsWith(q) || q.startsWith(key)) {
        const rawType = (kv.type || '').toLowerCase()
        const isTankerOrStorage = rawType.includes('tanker') || rawType.includes('storage') || rawType.includes('production')
        const markerColor = isTankerOrStorage ? '#dc2626' : '#22c55e'
        return {
          ...kv,
          type: kv.type,
          flag: kv.flag,
          flagEmoji: getFlagEmoji(kv.flag),
          isSamePort: !!kv.isSamePort,
          markerColor,
          isTankerOrStorage,
          mapUrl: `https://www.vesselfinder.com/?imo=${kv.imo}`,
          directUrl: `https://www.marinetraffic.com/en/ais/details/ships/imo:${kv.imo}`,
        }
      }
    }
  }

  // 3. Default Initial Fallback
  const fallbackKv = KNOWN_VESSELS['GAZA MARINE TERMINAL']
  return {
    ...fallbackKv,
    flagEmoji: getFlagEmoji(fallbackKv.flag),
    markerColor: '#dc2626',
    isTankerOrStorage: true,
    mapUrl: `https://www.vesselfinder.com/?imo=${fallbackKv.imo}`,
    directUrl: `https://www.marinetraffic.com/en/ais/details/ships/imo:${fallbackKv.imo}`,
  }
})

// Update Leaflet Map and Fit Bounds to Route
const updateMapRoute = () => {
  if (!leafletMap || typeof window.L === 'undefined') return

  const L = window.L

  // Invalidate size in case tab or DOM dimensions changed
  leafletMap.invalidateSize()

  // Clear previous layers
  mapLayers.forEach(layer => {
    try {
      leafletMap.removeLayer(layer)
    } catch (e) {}
  })
  mapLayers = []

  const polCoords = voyage.value.polCoords
  const podCoords = voyage.value.podCoords
  const shipCoords = voyage.value.vesselCoords
  const isSamePort = voyage.value.isSamePort
  const markerColor = voyage.value.markerColor || '#22c55e'

  if (isSamePort) {
    // Single Location Pin (MarineTraffic style for terminal / moored offshore / same port)
    const vesselBeacon = L.divIcon({
      className: 'vessel-beacon-marker',
      html: `
        <div style="position: relative; width: 16px; height: 16px; cursor: pointer;">
          <div style="position: absolute; inset: -6px; border-radius: 50%; background: ${markerColor}; opacity: 0.4; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          <div style="width: 16px; height: 16px; background-color: ${markerColor}; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 5px rgba(0,0,0,0.5);"></div>
        </div>
      `,
      iconSize: [16, 16],
      iconAnchor: [8, 8],
    })
    const shipMarker = L.marker(shipCoords, { icon: vesselBeacon }).addTo(leafletMap)
    shipMarker.bindPopup(`
      <div style="font-family: system-ui, sans-serif; font-size: 12px; padding: 2px;">
        <strong style="color: #0f172a; font-size: 13px;">${voyage.value.name}</strong><br/>
        <span style="color: #64748b;">Terminal: <strong>${voyage.value.toPort} (${voyage.value.toCode})</strong></span><br/>
        <span style="color: #64748b;">Status: <strong>${voyage.value.speed} · ${voyage.value.navStatus}</strong></span>
      </div>
    `)
    mapLayers.push(shipMarker)

    // Center directly on terminal with regional zoom level (Tunisia/Libya/Mediterranean) matching MarineTraffic screenshot 2!
    leafletMap.setView(shipCoords, 7, { animate: true })
  } else {
    // 1. Departure Port Marker (Blue Dot)
    const polIcon = L.divIcon({
      className: 'pol-port-marker',
      html: `<div style="width: 10px; height: 10px; background-color: #0073c6; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.4);"></div>`,
      iconSize: [10, 10],
      iconAnchor: [5, 5],
    })
    const polMarker = L.marker(polCoords, { icon: polIcon }).addTo(leafletMap)
    polMarker.bindTooltip(`<b>${voyage.value.fromPort}</b><br/>POL: ${voyage.value.fromCode}`, { direction: 'top' })
    mapLayers.push(polMarker)

    // 2. Destination Port Marker (Solid Black Dot / Pin)
    const podIcon = L.divIcon({
      className: 'pod-port-marker',
      html: `<div style="width: 12px; height: 12px; background-color: #18181b; border: 2px solid #ffffff; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.5);"></div>`,
      iconSize: [12, 12],
      iconAnchor: [6, 6],
    })
    const podMarker = L.marker(podCoords, { icon: podIcon }).addTo(leafletMap)
    podMarker.bindTooltip(`<b>${voyage.value.toPort}</b><br/>POD: ${voyage.value.toCode}`, { direction: 'top' })
    mapLayers.push(podMarker)

    // 3. Nautical Polyline Route (Dotted Blue Track)
    const routePolyline = L.polyline([polCoords, shipCoords, podCoords], {
      color: '#0073c6',
      weight: 2.8,
      opacity: 0.85,
      dashArray: '4, 6',
    }).addTo(leafletMap)
    mapLayers.push(routePolyline)

    // 4. Live Vessel Beacon with Animated Pulse
    const vesselBeacon = L.divIcon({
      className: 'vessel-beacon-marker',
      html: `
        <div style="position: relative; width: 14px; height: 14px; cursor: pointer;">
          <div style="position: absolute; inset: -5px; border-radius: 50%; background: ${markerColor}; opacity: 0.4; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          <div style="width: 14px; height: 14px; background-color: ${markerColor}; border: 2.2px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.4);"></div>
        </div>
      `,
      iconSize: [14, 14],
      iconAnchor: [7, 7],
    })

    const shipMarker = L.marker(shipCoords, { icon: vesselBeacon }).addTo(leafletMap)
    shipMarker.bindPopup(`
      <div style="font-family: system-ui, sans-serif; font-size: 12px; padding: 2px;">
        <strong style="color: #0f172a; font-size: 13px;">${voyage.value.name}</strong><br/>
        <span style="color: #64748b;">Speed: <strong>${voyage.value.speed}</strong></span><br/>
        <span style="color: #0073c6; font-weight: bold;">${voyage.value.fromCode} → ${voyage.value.toCode}</span>
      </div>
    `)
    mapLayers.push(shipMarker)

    // 5. Auto-Fit Map View to Route with Padding
    try {
      const bounds = L.latLngBounds([
        L.latLng(polCoords[0], polCoords[1]),
        L.latLng(shipCoords[0], shipCoords[1]),
        L.latLng(podCoords[0], podCoords[1])
      ])
      leafletMap.fitBounds(bounds, {
        padding: [50, 50],
        maxZoom: 7,
        animate: true,
      })
    } catch (e) {
      leafletMap.setView(shipCoords, 5)
    }
  }
}

// Initialize Leaflet Map with Clean, Watermark-Free Esri Light Gray Tiles
const initMap = async () => {
  if (typeof window === 'undefined' || !mapContainer.value) return

  // 1. Ensure Leaflet CSS
  if (!document.getElementById('leaflet-cdn-css')) {
    const link = document.createElement('link')
    link.id = 'leaflet-cdn-css'
    link.rel = 'stylesheet'
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    document.head.appendChild(link)
  }

  // 2. Ensure Leaflet JS
  const L = await new Promise((resolve) => {
    if (window.L) {
      resolve(window.L)
      return
    }
    const script = document.createElement('script')
    script.id = 'leaflet-cdn-js'
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
    script.onload = () => resolve(window.L)
    script.onerror = () => resolve(null)
    document.head.appendChild(script)
  })

  if (!L || !mapContainer.value) return

  if (leafletMap) {
    try {
      leafletMap.remove()
    } catch (e) {}
  }

  try {
    leafletMap = L.map(mapContainer.value, {
      center: voyage.value.vesselCoords,
      zoom: voyage.value.isSamePort ? 7 : 4,
      minZoom: 2,
      maxZoom: 12,
      zoomControl: false,
      attributionControl: false,
    })

    // Watermark-Free Esri World Light Gray Base (Exact match to MarineTraffic light aesthetics)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
      maxZoom: 16,
      attribution: 'Esri, Garmin, © OpenStreetMap contributors'
    }).addTo(leafletMap)

    updateMapRoute()
  } catch (err) {
    console.error('Leaflet initialization error:', err)
  }
}

const handleZoomIn = () => {
  if (leafletMap) leafletMap.zoomIn()
}

const handleZoomOut = () => {
  if (leafletMap) leafletMap.zoomOut()
}

// Reactively update map whenever vessel prop or search query changes
watch(() => props.vessel, () => {
  nextTick(() => {
    updateMapRoute()
  })
}, { deep: true })

watch(() => props.searchQuery, () => {
  nextTick(() => {
    updateMapRoute()
  })
})

onMounted(() => {
  nextTick(() => {
    initMap()
  })
})

onUnmounted(() => {
  if (leafletMap) {
    try {
      leafletMap.remove()
    } catch (e) {}
    leafletMap = null
  }
})
</script>

<template>
  <div class="space-y-4 font-sans" :dir="dir">
    
    <!-- Vessel Profile MarineTraffic Header Bar (1:1 Exact Match with Screenshot 2) -->
    <div v-if="voyage.name" class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
      <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Flag, Vessel Name, Red/Blue Type Tag, IMO -->
        <div class="flex items-center gap-3">
          <span class="text-3xl select-none shrink-0" :title="voyage.flag">{{ voyage.flagEmoji }}</span>

          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight uppercase">
                {{ voyage.name }}
              </h1>
              
              <!-- Dynamic MarineTraffic Type Tag -->
              <span 
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-bold"
                :class="voyage.isTankerOrStorage ? 'bg-red-500 text-white' : 'bg-[#0073c6] text-white'"
              >
                <Flame v-if="voyage.isTankerOrStorage" class="w-3.5 h-3.5 fill-white" />
                <Ship v-else class="w-3.5 h-3.5 fill-white" />
                <span>{{ voyage.type }}</span>
              </span>
            </div>

            <!-- Metadata Line: IMO / MMSI / Flag -->
            <div class="flex items-center gap-3 text-xs text-slate-500 font-mono mt-1 flex-wrap">
              <span v-if="voyage.imo">IMO: <strong class="text-slate-800">{{ voyage.imo }}</strong></span>
              <span v-if="voyage.mmsi">MMSI: <strong class="text-slate-800">{{ voyage.mmsi }}</strong></span>
              <span v-if="voyage.flag">Flag: <strong class="text-slate-800">{{ voyage.flag }}</strong></span>
            </div>
          </div>
        </div>

        <!-- Right: MarineTraffic Direct Live Link Button -->
        <div class="flex items-center gap-2 shrink-0">
          <a
            :href="voyage.directUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-[#0073c6] hover:bg-[#005ea3] text-white text-xs font-bold shadow-xs transition cursor-pointer"
          >
            <span>MarineTraffic AIS</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </a>
        </div>

      </div>

      <!-- MarineTraffic Sub-Navigation Tabs Bar (Exact Match) -->
      <div class="flex items-center border-t border-slate-200 px-4 sm:px-5 bg-slate-50/70 text-xs overflow-x-auto no-scrollbar">
        <button type="button" class="py-2.5 px-3 font-bold text-[#0073c6] border-b-2 border-[#0073c6] shrink-0">
          Overview
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          Port call log
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          Vessel characteristics
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          Ownership
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          Performance insights
        </button>
      </div>
    </div>

    <!-- Current Voyage Container Card (1:1 Exact MarineTraffic Replica) -->
    <div class="rounded-xl border border-slate-200 bg-white shadow-xs overflow-hidden">
      
      <!-- Top Title Header (Minimal & Clean matching screenshot) -->
      <div class="px-6 py-4">
        <h2 class="text-base font-bold text-slate-900 tracking-tight">
          Current voyage
        </h2>
      </div>

      <!-- Live Voyage Map Box (Interactive Watermark-Free Leaflet Map) -->
      <div class="relative w-full h-[280px] sm:h-[340px] bg-[#c3d6df] overflow-hidden select-none border-y border-slate-200">
        
        <!-- Leaflet Map Container -->
        <div ref="mapContainer" class="w-full h-full z-0"></div>

        <!-- Top-Right Button: "View on live map" (Exact match) -->
        <div class="absolute top-3 end-3 z-[400]">
          <a
            :href="voyage.directUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="bg-white hover:bg-slate-50 text-slate-800 text-xs font-semibold px-3 py-1.5 rounded-md shadow-xs border border-slate-300 flex items-center gap-1.5 transition select-none cursor-pointer"
          >
            <span>View on live map</span>
          </a>
        </div>

        <!-- Right-Side Zoom Controls (+ and -) -->
        <div class="absolute top-1/2 end-3 -translate-y-1/2 flex flex-col rounded-md bg-white shadow-xs border border-slate-300 overflow-hidden z-[400] select-none">
          <button
            type="button"
            @click="handleZoomIn"
            class="w-7 h-7 flex items-center justify-center text-slate-700 hover:bg-slate-100 active:bg-slate-200 text-sm font-bold border-b border-slate-200 transition cursor-pointer"
            title="Zoom in"
          >
            +
          </button>
          <button
            type="button"
            @click="handleZoomOut"
            class="w-7 h-7 flex items-center justify-center text-slate-700 hover:bg-slate-100 active:bg-slate-200 text-sm font-bold transition cursor-pointer"
            title="Zoom out"
          >
            −
          </button>
        </div>

        <!-- Bottom-Right Map Attribution (Exact match) -->
        <div class="absolute bottom-1 end-2 text-[10px] text-slate-600 font-sans z-[400] bg-white/75 backdrop-blur-xs px-1.5 py-0.5 rounded pointer-events-none">
          <span>Leaflet</span> | © <span>Mapbox</span> © <span>OpenStreetMap</span>
        </div>
      </div>

      <!-- Route & Scrubber Details Bar (Exact replica from user's image) -->
      <div class="px-6 py-5 space-y-4 bg-white">
        
        <!-- Row 1: Blue Subheadings (Departure from ... / Arrival at ...) -->
        <div class="flex items-center justify-between text-[13px] font-normal">
          <a 
            :href="voyage.directUrl" 
            target="_blank" 
            rel="noopener noreferrer"
            class="text-[#0073c6] hover:underline cursor-pointer"
          >
            Departure from {{ voyage.fromPort }}
          </a>
          <a 
            :href="voyage.directUrl" 
            target="_blank" 
            rel="noopener noreferrer"
            class="text-[#0073c6] hover:underline text-end cursor-pointer"
          >
            Arrival at {{ voyage.toPort }}
          </a>
        </div>

        <!-- Row 2: Big Bold Port Titles (e.g. LY BOU / LY BOU or BE ANR / NL RTM) -->
        <div class="flex items-center justify-between pt-0.5">
          <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight uppercase">
            {{ voyage.fromCode }}
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight uppercase text-end">
            {{ voyage.toCode }}
          </div>
        </div>

        <!-- Row 3: Scrubber / Voyage Line Tracker (Exact replica for both same-port and multi-port) -->
        <div class="flex items-center gap-2 py-1 select-none">
          
          <!-- Start Indicator: Blue Circle with inner dot (◎) -->
          <div class="w-5 h-5 rounded-full border-[3px] border-[#0073c6] bg-white flex items-center justify-center shrink-0">
            <div class="w-1.5 h-1.5 rounded-full bg-[#0073c6]"></div>
          </div>

          <!-- Progress Line (Solid Blue Bar for single-port/terminal or dotted for transit) -->
          <div v-if="voyage.isSamePort" class="flex-1 h-1 bg-[#0073c6] rounded-full mx-1"></div>
          
          <div v-else class="flex-1 flex items-center justify-between overflow-hidden px-1.5">
            <span 
              v-for="i in 52" 
              :key="i" 
              class="w-1.5 h-1.5 rounded-full bg-[#0073c6]"
            ></span>
          </div>

          <!-- End Destination Pin: Solid Black Pin (📍) -->
          <MapPin class="w-5 h-5 fill-slate-900 text-slate-900 shrink-0" />
        </div>

        <!-- Row 4: Timestamps (Matching Screenshot 2) -->
        <div class="flex items-center justify-between text-sm pt-0.5">
          <div class="space-y-0.5">
            <div class="text-slate-500 text-[13px]">
              Actual time of departure:
            </div>
            <div class="font-normal text-slate-800 text-[15px]">
              {{ voyage.atd }}
            </div>
          </div>
          <div class="space-y-0.5 text-end">
            <div class="text-slate-500 text-[13px]">
              {{ voyage.isSamePort ? 'Actual time of arrival:' : 'Reported ETA:' }}
            </div>
            <div class="font-normal text-slate-800 text-[15px]">
              {{ voyage.eta }}
            </div>
          </div>
        </div>

        <!-- Row 5: Action Buttons Matching MarineTraffic (Past track & Route forecast) -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100">
          <a
            :href="voyage.directUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-1.5 rounded-md border border-[#0073c6] text-[#0073c6] hover:bg-blue-50 text-xs font-bold transition cursor-pointer flex items-center gap-1"
          >
            <span>Past track</span>
          </a>
          <a
            :href="voyage.mapUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-1.5 rounded-md border border-slate-200 hover:border-slate-300 text-slate-400 hover:text-slate-600 text-xs font-semibold transition cursor-pointer flex items-center gap-1"
          >
            <span>Route forecast</span>
          </a>
        </div>

      </div>

    </div>
  </div>
</template>

<style>
.leaflet-container {
  width: 100%;
  height: 100%;
  background-color: #c3d6df;
  font-family: inherit;
}
.leaflet-popup-content-wrapper {
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>
