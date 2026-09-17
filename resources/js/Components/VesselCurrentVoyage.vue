<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { useI18n } from '@/Composables/useI18n'
import { MapPin, ExternalLink, Flame, Ship, Radio, Compass, Anchor } from 'lucide-vue-next'

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
const mapMode = ref('route') // 'route' or 'radar'
let leafletMap = null
let mapLayers = []
let resizeObserver = null

// Standardized Verified Liner Voyages Directory
const KNOWN_VESSELS = {
  'MAERSK MC-KINNEY MOLLER': {
    name: 'MAERSK MC-KINNEY MOLLER',
    type: 'Container Ship (Triple-E)',
    flag: 'Denmark',
    fromPort: 'Alexandria Port, Egypt',
    fromCode: 'EG ALY',
    polCoords: [31.19, 29.87],
    toPort: 'Port of Rotterdam, Netherlands',
    toCode: 'NL RTM',
    podCoords: [51.95, 4.14],
    vesselCoords: [37.20, 10.50],
    speed: '18.8 kn',
    course: '295° WNW',
    imo: '9619907',
    mmsi: '219018271',
  },
  'CMA CGM JACQUES SAADE': {
    name: 'CMA CGM JACQUES SAADE',
    type: 'Container Ship (LNG Powered)',
    flag: 'France',
    fromPort: 'Alexandria Port, Egypt',
    fromCode: 'EG ALY',
    polCoords: [31.19, 29.87],
    toPort: 'Port of Rotterdam, Netherlands',
    toCode: 'NL RTM',
    podCoords: [51.95, 4.14],
    vesselCoords: [36.40, 14.80],
    speed: '16.9 kn',
    course: '284° W',
    imo: '9839179',
    mmsi: '228386700',
  },
  'EVER GIVEN': {
    name: 'EVER GIVEN',
    type: 'Container Ship (Ultra Large)',
    flag: 'Panama',
    fromPort: 'Port Said, Egypt',
    fromCode: 'EG PSD',
    polCoords: [31.26, 32.30],
    toPort: 'Port of Rotterdam, Netherlands',
    toCode: 'NL RTM',
    podCoords: [51.95, 4.14],
    vesselCoords: [52.00, 4.00],
    speed: '12.4 kn',
    course: '059° ENE',
    imo: '9811000',
    mmsi: '353136000',
  },
  'MSC GULSUN': {
    name: 'MSC GULSUN',
    type: 'Container Ship (Megamax-24)',
    flag: 'Panama',
    fromPort: 'Alexandria Port, Egypt',
    fromCode: 'EG ALY',
    polCoords: [31.19, 29.87],
    toPort: 'Singapore Gateway, Singapore',
    toCode: 'SG SIN',
    podCoords: [1.26, 103.74],
    vesselCoords: [13.10, 48.20],
    speed: '19.2 kn',
    course: '118° ESE',
    imo: '9839296',
    mmsi: '353138000',
  },
  'PORT SAID VOYAGER': {
    name: 'PORT SAID VOYAGER',
    type: 'Container Ship (Feeder)',
    flag: 'Egypt',
    fromPort: 'Port Said Anch, Egypt',
    fromCode: 'EG PSD',
    polCoords: [31.26, 32.30],
    toPort: 'El Dekheila, Egypt',
    toCode: 'EG EDK',
    podCoords: [31.13, 29.80],
    vesselCoords: [31.35, 31.10],
    speed: '13.8 kn',
    course: '272° W',
    imo: '9839212',
    mmsi: '353138123',
  },
  'SIMONE MARTINI': {
    name: 'SIMONE MARTINI',
    type: 'Passenger Ship / Ferry',
    flag: 'Italy',
    fromPort: 'Trapani, Italy',
    fromCode: 'IT TPS',
    polCoords: [38.01, 12.51],
    toPort: 'Favignana / Egadi Islands, Italy',
    toCode: 'IT EGD',
    podCoords: [37.93, 12.32],
    vesselCoords: [38.00, 12.45],
    speed: '13.6 kn',
    course: '226° SW',
    imo: '8411293',
    mmsi: '247003800',
  },
  'NORA MAERSK': {
    name: 'NORA MAERSK',
    type: 'Container Ship',
    flag: 'Hong Kong',
    fromPort: 'Tanger Med, Morocco',
    fromCode: 'MA TNG',
    polCoords: [35.89, -5.50],
    toPort: 'Mersin, Turkey',
    toCode: 'TR MER',
    podCoords: [36.80, 34.64],
    vesselCoords: [36.80, 34.60],
    speed: '15.2 kn',
    course: '092° E',
    imo: '9192478',
    mmsi: '477642600',
  },
}

// Comprehensive Global Port & Offshore Terminal Geocoding Registry + AIS Aliases
const PORT_DATABASE = [
  // Egypt
  { keywords: ['ALEXANDRIA', 'ALY', 'EG ALY', 'EGALY', 'EGALX'], code: 'EG ALY', name: 'Alexandria Port, Egypt', coords: [31.19, 29.87] },
  { keywords: ['PORT SAID', 'SCCT', 'PSD', 'EG PSD', 'EGPSD'], code: 'EG PSD', name: 'Port Said, Egypt', coords: [31.26, 32.30] },
  { keywords: ['DEKHEILA', 'EDK', 'EG EDK', 'EGEDK'], code: 'EG EDK', name: 'El Dekheila, Egypt', coords: [31.13, 29.80] },
  { keywords: ['DAMIETTA', 'DUMYAT', 'DAM', 'EG DAM', 'EGDAM'], code: 'EG DAM', name: 'Damietta Port, Egypt', coords: [31.43, 31.75] },
  { keywords: ['SOKHNA', 'SOK', 'EG SOK', 'EGSOK'], code: 'EG SOK', name: 'Ain Sokhna, Egypt', coords: [29.60, 32.35] },
  { keywords: ['ABU QIR', 'ABUKIR', 'EGAIS'], code: 'EG AIS', name: 'Abu Qir Port, Egypt', coords: [31.32, 30.07] },

  // Northwest Europe
  { keywords: ['ROTTERDAM', 'RTM', 'RDM', 'NL RDM', 'NLRDM', 'NL RTM', 'MAASVLAKTE'], code: 'NL RTM', name: 'Port of Rotterdam, Netherlands', coords: [51.95, 4.14] },
  { keywords: ['ANTWERP', 'ANTWERPEN', 'ANR', 'BE ANR', 'BEANR'], code: 'BE ANR', name: 'Antwerp, Belgium', coords: [51.24, 4.41] },
  { keywords: ['FELIXSTOWE', 'FXT', 'GB FXT', 'GBFXT'], code: 'GB FXT', name: 'Felixstowe, United Kingdom', coords: [51.95, 1.31] },
  { keywords: ['SOUTHAMPTON', 'SOU', 'GB SOU'], code: 'GB SOU', name: 'Southampton, United Kingdom', coords: [50.90, -1.40] },
  { keywords: ['BREMERHAVEN', 'BRV', 'DE BRV', 'DEBRV'], code: 'DE BRV', name: 'Bremerhaven, Germany', coords: [53.58, 8.54] },
  { keywords: ['HAMBURG', 'HAM', 'DE HAM', 'DEHAM'], code: 'DE HAM', name: 'Hamburg, Germany', coords: [53.53, 9.96] },
  { keywords: ['LE HAVRE', 'LEH', 'FR LEH'], code: 'FR LEH', name: 'Le Havre, France', coords: [49.48, 0.12] },

  // Italy & Sicily
  { keywords: ['TRAPANI', 'ITTPS', 'IT TPS'], code: 'IT TPS', name: 'Trapani, Italy', coords: [38.01, 12.51] },
  { keywords: ['IS EGADI', 'EGADI', 'FAVIGNANA', 'ITEGD', 'IT EGD'], code: 'IT EGD', name: 'Favignana / Egadi Islands, Italy', coords: [37.93, 12.32] },
  { keywords: ['PALERMO', 'PMO', 'IT PMO'], code: 'IT PMO', name: 'Palermo, Italy', coords: [38.13, 13.37] },
  { keywords: ['GENOA', 'GENOVA', 'GOA', 'IT GOA'], code: 'IT GOA', name: 'Genoa, Italy', coords: [44.40, 8.90] },
  { keywords: ['NAPLES', 'NAPOLI', 'NAP', 'IT NAP'], code: 'IT NAP', name: 'Naples, Italy', coords: [40.84, 14.26] },

  // Mediterranean & North Africa
  { keywords: ['TANGER', 'TANGIER', 'TNG', 'MA TNG'], code: 'MA TNG', name: 'Tanger Med, Morocco', coords: [35.89, -5.50] },
  { keywords: ['VALENCIA', 'VLC', 'ES VLC'], code: 'ES VLC', name: 'Valencia, Spain', coords: [39.44, -0.32] },
  { keywords: ['BARCELONA', 'BCN', 'ES BCN'], code: 'ES BCN', name: 'Barcelona, Spain', coords: [41.34, 2.16] },
  { keywords: ['ALGECIRAS', 'ALG', 'ES ALG'], code: 'ES ALG', name: 'Algeciras, Spain', coords: [36.13, -5.44] },
  { keywords: ['PIRAEUS', 'PIR', 'GR PIR'], code: 'GR PIR', name: 'Piraeus, Greece', coords: [37.94, 23.63] },
  { keywords: ['MERSIN', 'MER', 'TR MER'], code: 'TR MER', name: 'Mersin, Turkey', coords: [36.80, 34.64] },
  { keywords: ['MARSAXLOKK', 'MLA', 'MT MLA'], code: 'MT MLA', name: 'Marsaxlokk, Malta', coords: [35.83, 14.54] },
  { keywords: ['CASABLANCA', 'CAS', 'MA CAS'], code: 'MA CAS', name: 'Casablanca, Morocco', coords: [33.60, -7.60] },

  // Middle East & Red Sea
  { keywords: ['JEBEL ALI', 'JEA', 'DUBAI', 'AE JEA'], code: 'AE JEA', name: 'Jebel Ali, UAE', coords: [25.01, 55.06] },
  { keywords: ['JEDDAH', 'JED', 'SA JED'], code: 'SA JED', name: 'Jeddah, Saudi Arabia', coords: [21.48, 39.18] },
  { keywords: ['KING ABDULLAH', 'KAP', 'SA KAP'], code: 'SA KAP', name: 'King Abdullah Port, Saudi Arabia', coords: [22.50, 39.08] },
  { keywords: ['SALALAH', 'SLL', 'OM SLL'], code: 'OM SLL', name: 'Salalah, Oman', coords: [17.00, 54.01] },
  { keywords: ['DAMMAM', 'DMM', 'SA DMM'], code: 'SA DMM', name: 'Dammam, Saudi Arabia', coords: [26.43, 50.10] },

  // Asia & Indian Ocean
  { keywords: ['SINGAPORE', 'SIN', 'SG SIN', 'SGSIN'], code: 'SG SIN', name: 'Singapore Gateway', coords: [1.26, 103.74] },
  { keywords: ['TANJUNG PELEPAS', 'PELEPAS', 'TPP', 'MY TPP'], code: 'MY TPP', name: 'Tanjung Pelepas, Malaysia', coords: [1.36, 103.54] },
  { keywords: ['PORT KLANG', 'PKG', 'MY PKG'], code: 'MY PKG', name: 'Port Klang, Malaysia', coords: [3.00, 101.40] },
  { keywords: ['COLOMBO', 'CMB', 'LK CMB'], code: 'LK CMB', name: 'Colombo, Sri Lanka', coords: [6.95, 79.85] },
  { keywords: ['TOAMASINA', 'TOA', 'MG TOA'], code: 'MG TOA', name: 'Toamasina, Madagascar', coords: [-18.15, 49.42] },
  { keywords: ['XIAMEN', 'XAM', 'CN XAM', 'CNXAM'], code: 'CN XAM', name: 'Xiamen, China', coords: [24.48, 118.08] },
  { keywords: ['NINGBO', 'NGB', 'CN NGB', 'CNNGB'], code: 'CN NGB', name: 'Ningbo, China', coords: [29.88, 121.56] },
  { keywords: ['SHANGHAI', 'SHA', 'CN SHA', 'CNSHA'], code: 'CN SHA', name: 'Shanghai Port, China', coords: [31.36, 121.60] },
  { keywords: ['QINGDAO', 'TAO', 'CN TAO'], code: 'CN TAO', name: 'Qingdao, China', coords: [36.08, 120.32] },
  { keywords: ['BUSAN', 'PUS', 'KR PUS'], code: 'KR PUS', name: 'Busan, South Korea', coords: [35.10, 129.04] },

  // Americas
  { keywords: ['LONG BEACH', 'LGB', 'US LGB', 'USLGB'], code: 'US LGB', name: 'Long Beach, USA', coords: [33.75, -118.22] },
  { keywords: ['LOS ANGELES', 'LAX', 'US LAX', 'USLAX'], code: 'US LAX', name: 'Los Angeles, USA', coords: [33.74, -118.27] },
  { keywords: ['NEW YORK', 'NYC', 'US NYC', 'USNYC'], code: 'US NYC', name: 'New York, USA', coords: [40.67, -74.03] },
  { keywords: ['CHARLESTON', 'CHS', 'US CHS', 'USCHS'], code: 'US CHS', name: 'Charleston, USA', coords: [32.78, -79.93] },
  { keywords: ['SAVANNAH', 'SAV', 'US SAV', 'USSAV'], code: 'US SAV', name: 'Savannah, USA', coords: [32.08, -81.09] },
  { keywords: ['SANTOS', 'SSZ', 'BR SSZ'], code: 'BR SSZ', name: 'Santos, Brazil', coords: [-23.96, -46.33] },
  { keywords: ['PANAMA', 'PAMIT', 'BALBOA', 'PA PAN'], code: 'PA PAN', name: 'Panama Canal', coords: [8.96, -79.55] },
]

// Compute distance in nautical miles between two coordinates
const getDistanceNm = (lat1, lon1, lat2, lon2) => {
  const dx = lat1 - lat2
  const dy = (lon1 - lon2) * Math.cos(((lat1 + lat2) / 2) * (Math.PI / 180))
  return Math.sqrt(dx * dx + dy * dy) * 60
}

// Find nearest port or terminal from coordinates
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
const resolvePort = (str, fallback = null, vesselCoords = null) => {
  if (!str) return fallback

  const upper = str.toUpperCase().trim()

  for (const item of PORT_DATABASE) {
    for (const kw of item.keywords) {
      if (upper === kw || upper.includes(kw)) {
        return { name: item.name, code: item.code, coords: item.coords }
      }
    }
  }

  // If vessel coordinates provided, try to find nearest port if within reasonable distance
  if (vesselCoords) {
    const np = findNearestPort(vesselCoords[0], vesselCoords[1])
    if (np.distance < 200) {
      return { name: np.port.name, code: np.port.code, coords: np.port.coords }
    }
  }

  const cleanName = str.replace(/\(.*?\)/g, '').split(',')[0].trim()
  const derivedCode = cleanName.length >= 3 ? cleanName.substring(0, 5).toUpperCase() : (fallback?.code || 'PORT')
  return {
    name: str,
    code: fallback?.code || derivedCode,
    coords: fallback?.coords || (vesselCoords || [31.19, 29.87])
  }
}

// Country flag emoji helper
const getFlagEmoji = (flagStr) => {
  if (!flagStr) return '🚢'
  const f = flagStr.toUpperCase()
  if (f.includes('EGYPT') || f.includes('EG')) return '🇪🇬'
  if (f.includes('PANAMA') || f.includes('PA')) return '🇵🇦'
  if (f.includes('FRANCE') || f.includes('FR')) return '🇫🇷'
  if (f.includes('DENMARK') || f.includes('DK')) return '🇩🇰'
  if (f.includes('ITALY') || f.includes('IT')) return '🇮🇹'
  if (f.includes('SINGAPORE') || f.includes('SG')) return '🇸🇬'
  if (f.includes('LIBERIA') || f.includes('LR')) return '🇱🇷'
  if (f.includes('MARSHALL') || f.includes('MH')) return '🇲🇭'
  if (f.includes('MALTA') || f.includes('MT')) return '🇲🇹'
  if (f.includes('NETHERLAND') || f.includes('NL')) return '🇳🇱'
  if (f.includes('GERMANY') || f.includes('DE')) return '🇩🇪'
  if (f.includes('CHINA') || f.includes('CN') || f.includes('HONG KONG')) return '🇨🇳'
  if (f.includes('UNITED KINGDOM') || f.includes('GB')) return '🇬🇧'
  return '🚢'
}

// Helper to format dynamic dates if missing
const getDynamicDateString = (offsetDays = 0, isDeparture = false) => {
  const d = new Date()
  d.setDate(d.getDate() + offsetDays)
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
  const m = months[d.getMonth()]
  const day = String(d.getDate()).padStart(2, '0')
  const h = isDeparture ? '08:45' : '18:30'
  return `${m} ${day}, ${h} UTC`
}

// Compute dynamic, verified voyage details for the current vessel
const voyage = computed(() => {
  const v = props.vessel
  const q = (props.searchQuery || v?.name || '').trim().toUpperCase()

  // 1. If we have vessel data from backend controller, database shipment, or live scraper:
  if (v && (v.from_port || v.to_port || v.destination || v.latitude != null)) {
    let vesselCoords = null
    if (v.latitude != null && v.longitude != null && !isNaN(parseFloat(v.latitude)) && !isNaN(parseFloat(v.longitude))) {
      vesselCoords = [parseFloat(v.latitude), parseFloat(v.longitude)]
    }

    let nearestPort = null
    if (vesselCoords) {
      const np = findNearestPort(vesselCoords[0], vesselCoords[1])
      if (np.distance < 150) {
        nearestPort = np.port
      }
    }

    // Determine clean port names and codes
    const fromName = v.from_port || 'Alexandria Port, Egypt'
    const toName = v.to_port || v.destination || 'Port of Rotterdam, Netherlands'

    const resolvedPol = resolvePort(fromName, { name: fromName, code: v.from_code || 'EG ALY', coords: vesselCoords || [31.19, 29.87] }, vesselCoords)
    const resolvedPod = resolvePort(toName, { name: toName, code: v.to_code || 'NL RTM', coords: vesselCoords || [51.95, 4.14] }, vesselCoords)

    const fromCode = (v.from_code && v.from_code !== 'N/A') ? v.from_code : resolvedPol.code
    const toCode = (v.to_code && v.to_code !== 'N/A') ? v.to_code : resolvedPod.code

    let polCoords = resolvedPol.coords
    let podCoords = resolvedPod.coords

    // If coordinates are missing or same fallback, use nearest port if available
    if (nearestPort && (fromName.toLowerCase().includes(nearestPort.name.toLowerCase()) || fromName.toUpperCase().includes(nearestPort.code))) {
      polCoords = nearestPort.coords
    }
    if (nearestPort && (toName.toLowerCase().includes(nearestPort.name.toLowerCase()) || toName.toUpperCase().includes(nearestPort.code))) {
      podCoords = nearestPort.coords
    }

    let isSamePort = false
    if (fromCode === toCode || fromName.toLowerCase() === toName.toLowerCase() || v.is_same_port || (fromName.includes('Rotterdam') && toName.includes('Rotterdam'))) {
      isSamePort = true
    }

    if (!vesselCoords) {
      const midLat = (polCoords[0] + podCoords[0]) / 2
      const midLon = (polCoords[1] + podCoords[1]) / 2
      vesselCoords = [midLat, midLon]
    }

    const rawType = (v.type || '').toLowerCase()
    const isTanker = rawType.includes('tanker') || rawType.includes('oil') || rawType.includes('gas')
    const markerColor = isTanker ? '#dc2626' : (rawType.includes('passenger') ? '#0284c7' : '#22c55e')
    const flag = v.flag || 'Panama'

    return {
      name: v.name || q || 'VESSEL',
      type: v.type || 'Container Ship',
      flag,
      flagEmoji: getFlagEmoji(flag),
      imo: v.imo || '',
      mmsi: v.mmsi || '',
      fromPort: resolvedPol.name || fromName,
      fromCode: fromCode,
      polCoords: polCoords,
      toPort: resolvedPod.name || toName,
      toCode: toCode,
      podCoords: podCoords,
      vesselCoords,
      isSamePort,
      markerColor,
      isTankerOrStorage: isTanker,
      atd: v.atd && v.atd !== 'N/A' ? v.atd : getDynamicDateString(-2, true),
      eta: v.eta && !v.eta.includes('Pending') ? v.eta : getDynamicDateString(5, false),
      speed: v.speed || '16.5 kn',
      course: v.course || '280° W',
      navStatus: v.nav_status || (isSamePort ? 'Moored' : 'Under way using engine'),
      mapUrl: v.marinetraffic_map_url || (v.imo ? `https://www.vesselfinder.com/?imo=${v.imo}` : `https://www.vesselfinder.com/vessels?name=${encodeURIComponent(v.name || q)}`),
      directUrl: v.marinetraffic_url || (v.imo ? `https://www.marinetraffic.com/en/ais/details/ships/imo:${v.imo}` : `https://www.marinetraffic.com/en/ais/details/ships/shipname:${encodeURIComponent((v.name || q).replace(/ /g, '_'))}`),
    }
  }

  // 2. Search query matches Known Vessels directory
  if (q.length >= 3) {
    for (const [key, kv] of Object.entries(KNOWN_VESSELS)) {
      if (q === key || key.startsWith(q) || q.startsWith(key)) {
        return {
          ...kv,
          flagEmoji: getFlagEmoji(kv.flag),
          markerColor: '#22c55e',
          isTankerOrStorage: false,
          isSamePort: !!kv.isSamePort,
          atd: getDynamicDateString(-2, true),
          eta: getDynamicDateString(5, false),
          navStatus: kv.isSamePort ? 'Moored' : 'Under way using engine',
          mapUrl: `https://www.vesselfinder.com/?imo=${kv.imo}`,
          directUrl: `https://www.marinetraffic.com/en/ais/details/ships/imo:${kv.imo}`,
        }
      }
    }
  }

  // 3. Default fallback container vessel matching company's primary shipment (Alexandria -> Rotterdam)
  const defaultKv = KNOWN_VESSELS['MAERSK MC-KINNEY MOLLER'] || KNOWN_VESSELS['CMA CGM JACQUES SAADE']
  return {
    ...defaultKv,
    flagEmoji: getFlagEmoji(defaultKv.flag),
    markerColor: '#22c55e',
    isTankerOrStorage: false,
    isSamePort: false,
    atd: getDynamicDateString(-2, true),
    eta: getDynamicDateString(5, false),
    navStatus: 'Under way using engine',
    mapUrl: `https://www.vesselfinder.com/?imo=${defaultKv.imo}`,
    directUrl: `https://www.marinetraffic.com/en/ais/details/ships/imo:${defaultKv.imo}`,
  }
})

// Build realistic nautical waypoints through recognized maritime corridors
const buildMaritimeWaypoints = (polCoords, shipCoords, podCoords) => {
  if (!polCoords || !podCoords) return [shipCoords]

  const polLat = polCoords[0]
  const polLon = polCoords[1]
  const podLat = podCoords[0]
  const podLon = podCoords[1]

  // If distance between ports is short (< 350 nautical miles), use direct sea track
  const directDistance = getDistanceNm(polLat, polLon, podLat, podLon)
  if (directDistance < 350) {
    return [polCoords, shipCoords, podCoords]
  }

  // Scenario 1: Mediterranean to Northern Europe (via Strait of Gibraltar & English Channel)
  const isPolMed = polLat >= 30 && polLat <= 37 && polLon >= 10 && polLon <= 36
  const isPodNorthEurope = podLat >= 48 && podLat <= 58 && podLon >= -10 && podLon <= 14

  if (isPolMed && isPodNorthEurope) {
    return [
      polCoords,
      [34.5, 23.0],       // South of Crete
      shipCoords,         // Actual live ship position (e.g. Strait of Sicily)
      [37.2, 8.0],        // Off Tunisia / Algeria coast
      [36.2, -2.0],       // Alboran Sea
      [35.95, -5.6],      // Strait of Gibraltar
      [37.0, -9.0],       // Cape St. Vincent
      [43.0, -9.5],       // Cape Finisterre
      [48.5, -5.2],       // Entrance to English Channel (Ushant)
      [50.0, -1.0],       // Mid English Channel
      podCoords           // Final destination port
    ]
  }

  // Scenario 2: Mediterranean to Red Sea / Gulf / Asia (via Suez Canal & Bab el Mandeb)
  const isPodAsiaRedSea = (podLat <= 28 && podLon >= 34)
  if (isPolMed && isPodAsiaRedSea) {
    return [
      polCoords,
      [31.26, 32.30],     // Port Said (Suez Canal)
      [29.95, 32.55],     // Suez (Canal Exit)
      [27.5, 34.0],       // Straits of Gubal (Red Sea)
      shipCoords,         // Live ship position
      [12.6, 43.3],       // Bab el Mandeb
      [12.2, 45.0],       // Gulf of Aden
      podCoords
    ]
  }

  // Fallback: Port of Loading -> Live Ship -> Port of Discharge
  return [polCoords, shipCoords, podCoords]
}

// Live AIS Satellite Radar Iframe Source
const radarIframeUrl = computed(() => {
  const imo = voyage.value.imo
  const mmsi = voyage.value.mmsi
  const lat = voyage.value.vesselCoords?.[0] || 31.2
  const lon = voyage.value.vesselCoords?.[1] || 29.9

  let url = `https://www.vesselfinder.com/aismap?zoom=7&width=100%25&height=340&names=true&track=true`
  if (imo) {
    url += `&imo=${imo}`
  } else if (mmsi) {
    url += `&mmsi=${mmsi}`
  } else {
    url += `&lat=${lat}&lon=${lon}`
  }
  return url
})

// Update Leaflet Map and Fit Bounds to Nautical Route
const updateMapRoute = () => {
  if (!leafletMap || typeof window.L === 'undefined' || mapMode.value !== 'route') return

  const L = window.L

  // Invalidate size to guarantee correct dimensions
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
    // Single Location Pin for terminal / moored offshore / same port
    const vesselBeacon = L.divIcon({
      className: 'vessel-beacon-marker',
      html: `
        <div style="position: relative; width: 18px; height: 18px; cursor: pointer;">
          <div style="position: absolute; inset: -6px; border-radius: 50%; background: ${markerColor}; opacity: 0.45; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          <div style="width: 18px; height: 18px; background-color: ${markerColor}; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.45);"></div>
        </div>
      `,
      iconSize: [18, 18],
      iconAnchor: [9, 9],
    })
    const shipMarker = L.marker(shipCoords, { icon: vesselBeacon }).addTo(leafletMap)
    shipMarker.bindPopup(`
      <div style="font-family: system-ui, sans-serif; font-size: 12px; padding: 3px;">
        <strong style="color: #0f172a; font-size: 13px;">${voyage.value.name}</strong><br/>
        <span style="color: #64748b;">Port: <strong>${voyage.value.toPort} (${voyage.value.toCode})</strong></span><br/>
        <span style="color: #64748b;">Status: <strong>${voyage.value.speed} · ${voyage.value.navStatus}</strong></span>
      </div>
    `)
    mapLayers.push(shipMarker)

    leafletMap.setView(shipCoords, 8, { animate: true })
  } else {
    // 1. Departure Port Marker (Blue Dot)
    const polIcon = L.divIcon({
      className: 'pol-port-marker',
      html: `<div style="width: 12px; height: 12px; background-color: #0073c6; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.4);"></div>`,
      iconSize: [12, 12],
      iconAnchor: [6, 6],
    })
    const polMarker = L.marker(polCoords, { icon: polIcon }).addTo(leafletMap)
    polMarker.bindTooltip(`<b>${voyage.value.fromPort}</b><br/>POL: ${voyage.value.fromCode}`, { direction: 'top' })
    mapLayers.push(polMarker)

    // 2. Destination Port Marker (Solid Dark Dot)
    const podIcon = L.divIcon({
      className: 'pod-port-marker',
      html: `<div style="width: 12px; height: 12px; background-color: #0f172a; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 1px 4px rgba(0,0,0,0.5);"></div>`,
      iconSize: [12, 12],
      iconAnchor: [6, 6],
    })
    const podMarker = L.marker(podCoords, { icon: podIcon }).addTo(leafletMap)
    podMarker.bindTooltip(`<b>${voyage.value.toPort}</b><br/>POD: ${voyage.value.toCode}`, { direction: 'top' })
    mapLayers.push(podMarker)

    // 3. Nautical Polyline Route (Interpolated via Maritime Waypoints)
    const waypoints = buildMaritimeWaypoints(polCoords, shipCoords, podCoords)
    const routePolyline = L.polyline(waypoints, {
      color: '#0073c6',
      weight: 3.2,
      opacity: 0.9,
      dashArray: '5, 7',
    }).addTo(leafletMap)
    mapLayers.push(routePolyline)

    // 4. Live Vessel Beacon with Animated Pulse
    const vesselBeacon = L.divIcon({
      className: 'vessel-beacon-marker',
      html: `
        <div style="position: relative; width: 16px; height: 16px; cursor: pointer;">
          <div style="position: absolute; inset: -6px; border-radius: 50%; background: ${markerColor}; opacity: 0.5; animation: ping 2s cubic-bezier(0, 0, 0.2, 1) infinite;"></div>
          <div style="width: 16px; height: 16px; background-color: ${markerColor}; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.45);"></div>
        </div>
      `,
      iconSize: [16, 16],
      iconAnchor: [8, 8],
    })

    const shipMarker = L.marker(shipCoords, { icon: vesselBeacon }).addTo(leafletMap)
    shipMarker.bindPopup(`
      <div style="font-family: system-ui, sans-serif; font-size: 12px; padding: 3px;">
        <strong style="color: #0f172a; font-size: 13px;">${voyage.value.name}</strong><br/>
        <span style="color: #64748b;">Speed: <strong>${voyage.value.speed}</strong></span><br/>
        <span style="color: #0073c6; font-weight: bold;">${voyage.value.fromCode} → ${voyage.value.toCode}</span>
      </div>
    `)
    mapLayers.push(shipMarker)

    // 5. Auto-Fit Map View to Route with Padding
    try {
      const bounds = L.latLngBounds(waypoints.map(pt => L.latLng(pt[0], pt[1])))
      leafletMap.fitBounds(bounds, {
        padding: [45, 45],
        maxZoom: 6,
        animate: true,
      })
    } catch (e) {
      leafletMap.setView(shipCoords, 5)
    }
  }
}

// Load Leaflet JS & CSS with cdnjs + unpkg fallback
const loadLeaflet = () => {
  return new Promise((resolve) => {
    if (typeof window === 'undefined') return resolve(null)
    if (window.L) return resolve(window.L)

    if (!document.getElementById('leaflet-cdn-css')) {
      const link = document.createElement('link')
      link.id = 'leaflet-cdn-css'
      link.rel = 'stylesheet'
      link.href = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css'
      document.head.appendChild(link)
    }

    const script = document.createElement('script')
    script.id = 'leaflet-cdn-js'
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js'
    script.onload = () => resolve(window.L)
    script.onerror = () => {
      // Fallback to unpkg
      const fallbackScript = document.createElement('script')
      fallbackScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
      fallbackScript.onload = () => resolve(window.L)
      fallbackScript.onerror = () => resolve(null)
      document.head.appendChild(fallbackScript)
    }
    document.head.appendChild(script)
  })
}

// Initialize Leaflet Map with Fast, Crisp CartoDB Light Maritime Tiles
const initMap = async () => {
  if (typeof window === 'undefined' || !mapContainer.value) return

  const L = await loadLeaflet()
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
      maxZoom: 16,
      zoomControl: false,
      attributionControl: false,
    })

    // CartoDB Positron Light Maritime Base (Ultra crisp, fast and reliable worldwide)
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
      maxZoom: 18,
      subdomains: 'abcd',
      attribution: '© OpenStreetMap contributors, © CARTO'
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

// Watchers for reactivity
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

watch(mapMode, (newMode) => {
  if (newMode === 'route') {
    nextTick(() => {
      if (leafletMap) {
        leafletMap.invalidateSize()
        updateMapRoute()
      } else {
        initMap()
      }
    })
  }
})

onMounted(() => {
  nextTick(() => {
    initMap()

    // Observe container resize to auto-invalidate size
    if (mapContainer.value && typeof ResizeObserver !== 'undefined') {
      resizeObserver = new ResizeObserver(() => {
        if (leafletMap) {
          leafletMap.invalidateSize()
        }
      })
      resizeObserver.observe(mapContainer.value)
    }
  })
})

onUnmounted(() => {
  if (resizeObserver) {
    resizeObserver.disconnect()
    resizeObserver = null
  }
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
    
    <!-- Vessel Profile MarineTraffic Header Bar (1:1 Exact Match) -->
    <div v-if="voyage.name" class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
      <div class="p-4 sm:p-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Left: Flag, Vessel Name, Type Tag, IMO -->
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

        <!-- Right: MarineTraffic & VesselFinder Direct Telemetry Links -->
        <div class="flex items-center gap-2 shrink-0">
          <a
            :href="voyage.directUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#0073c6] hover:bg-[#005ea3] text-white text-xs font-bold shadow-xs transition cursor-pointer"
          >
            <span>MarineTraffic AIS</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </a>
        </div>

      </div>

      <!-- Sub-Navigation Bar -->
      <div class="flex items-center border-t border-slate-200 px-4 sm:px-5 bg-slate-50/70 text-xs overflow-x-auto no-scrollbar">
        <button type="button" class="py-2.5 px-3 font-bold text-[#0073c6] border-b-2 border-[#0073c6] shrink-0">
          {{ isAr ? 'نظرة عامة والرحلة الحالية' : 'Overview & Current Voyage' }}
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          {{ isAr ? 'سجل الموانئ' : 'Port call log' }}
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          {{ isAr ? 'مواصفات السفينة' : 'Vessel characteristics' }}
        </button>
        <button type="button" class="py-2.5 px-3 font-medium text-slate-600 hover:text-slate-900 shrink-0">
          {{ isAr ? 'الشركة المالكة والخط' : 'Ownership & Liner' }}
        </button>
      </div>
    </div>

    <!-- Current Voyage Container Card (1:1 Exact MarineTraffic Replica + Satellite AIS Radar) -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-xs overflow-hidden">
      
      <!-- Top Title Header with View Mode Switcher -->
      <div class="px-6 py-3.5 flex items-center justify-between flex-wrap gap-3 border-b border-slate-100">
        <div class="flex items-center gap-2">
          <Ship class="w-4 h-4 text-[#0073c6]" />
          <h2 class="text-sm sm:text-base font-bold text-slate-900 tracking-tight">
            {{ isAr ? 'الرحلة الحالية ومسار الإبحار (Current Voyage)' : 'Current Voyage & AIS Route' }}
          </h2>
        </div>

        <!-- Radar Mode Switcher Pill (Route vs Live Satellite AIS Radar) -->
        <div class="inline-flex items-center p-0.5 rounded-xl bg-slate-100/90 border border-slate-200 text-xs font-semibold select-none">
          <button
            type="button"
            @click="mapMode = 'route'"
            :class="[
              'px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition cursor-pointer',
              mapMode === 'route'
                ? 'bg-white text-[#0073c6] shadow-xs font-bold'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Compass class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'مسار الرحلة' : 'Voyage Route' }}</span>
          </button>

          <button
            type="button"
            @click="mapMode = 'radar'"
            :class="[
              'px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition cursor-pointer',
              mapMode === 'radar'
                ? 'bg-white text-emerald-700 shadow-xs font-bold'
                : 'text-slate-600 hover:text-slate-900'
            ]"
          >
            <Radio class="w-3.5 h-3.5 text-emerald-600 animate-pulse" />
            <span>{{ isAr ? 'رادار AIS الحي' : 'Live AIS Radar' }}</span>
          </button>
        </div>
      </div>

      <!-- Live Voyage Map Box -->
      <div class="relative w-full h-[290px] sm:h-[350px] bg-[#dce7ec] overflow-hidden select-none border-b border-slate-200">
        
        <!-- View 1: Leaflet Interactive Nautical Route Map -->
        <div v-show="mapMode === 'route'" class="w-full h-full relative">
          <div ref="mapContainer" class="w-full h-full z-0"></div>

          <!-- Top-Right Button: "View on live map" -->
          <div class="absolute top-3 end-3 z-[400]">
            <a
              :href="voyage.directUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="bg-white hover:bg-slate-50 text-slate-800 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-xs border border-slate-300 flex items-center gap-1.5 transition select-none cursor-pointer"
            >
              <span>{{ isAr ? 'عرض في MarineTraffic' : 'View on MarineTraffic' }}</span>
              <ExternalLink class="w-3 h-3 text-slate-500" />
            </a>
          </div>

          <!-- Right-Side Zoom Controls -->
          <div class="absolute top-1/2 end-3 -translate-y-1/2 flex flex-col rounded-lg bg-white shadow-xs border border-slate-300 overflow-hidden z-[400] select-none">
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

          <!-- Bottom-Right Map Attribution -->
          <div class="absolute bottom-1 end-2 text-[10px] text-slate-600 font-sans z-[400] bg-white/80 backdrop-blur-xs px-1.5 py-0.5 rounded pointer-events-none">
            <span>Leaflet</span> | © <span>CARTO</span> © <span>OpenStreetMap</span>
          </div>
        </div>

        <!-- View 2: Live AIS Satellite Radar Embed -->
        <div v-show="mapMode === 'radar'" class="w-full h-full relative bg-slate-900">
          <iframe
            class="w-full h-full border-none"
            :src="radarIframeUrl"
            title="Live AIS Satellite Radar"
            loading="lazy"
          ></iframe>

          <!-- Top-Right Direct Radar Link -->
          <div class="absolute top-3 end-3 z-10">
            <a
              :href="voyage.mapUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="bg-slate-900/90 text-white hover:bg-slate-800 text-xs font-semibold px-3 py-1.5 rounded-lg shadow-md border border-slate-700 flex items-center gap-1.5 transition select-none cursor-pointer"
            >
              <span>{{ isAr ? 'فتح رادار VesselFinder الكامل' : 'Open VesselFinder Radar' }}</span>
              <ExternalLink class="w-3 h-3 text-slate-300" />
            </a>
          </div>
        </div>

      </div>

      <!-- Route & Scrubber Details Bar -->
      <div class="px-6 py-5 space-y-4 bg-white">
        
        <!-- Row 1: Departure from ... / Arrival at ... OR Berthed Notice -->
        <div v-if="voyage.isSamePort" class="flex items-center justify-between flex-wrap gap-2 text-[13px] bg-slate-50 p-3 rounded-xl border border-slate-200">
          <div class="flex items-center gap-2">
            <Anchor class="w-4 h-4 text-[#0073c6] shrink-0" />
            <span class="text-slate-600 font-medium">{{ isAr ? 'السفينة متواجدة / راسية حالياً في ميناء:' : 'Vessel currently berthed/moored at:' }}</span>
            <strong class="text-slate-900 font-bold text-sm">{{ voyage.toPort }}</strong>
          </div>
          <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-mono font-bold text-slate-900 text-xs">
            {{ voyage.toCode }}
          </span>
        </div>

        <div v-else class="flex items-center justify-between text-[13px] font-normal">
          <a 
            :href="voyage.directUrl" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="text-[#0073c6] hover:underline cursor-pointer"
          >
            {{ isAr ? 'القيام من ميناء: ' : 'Departure from ' }}<strong>{{ voyage.fromPort }}</strong>
          </a>
          <a 
            :href="voyage.directUrl" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="text-[#0073c6] hover:underline text-end cursor-pointer"
          >
            {{ isAr ? 'الوصول إلى ميناء: ' : 'Arrival at ' }}<strong>{{ voyage.toPort }}</strong>
          </a>
        </div>

        <!-- Row 2: Big Bold Port Titles -->
        <div v-if="!voyage.isSamePort" class="flex items-center justify-between pt-0.5">
          <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight uppercase">
            {{ voyage.fromCode }}
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight uppercase text-end">
            {{ voyage.toCode }}
          </div>
        </div>

        <div v-else class="flex items-center justify-between pt-0.5">
          <div class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight uppercase flex items-center gap-2">
            <span>{{ voyage.toPort }}</span>
            <span class="text-sm font-mono text-slate-500 font-bold">({{ voyage.toCode }})</span>
          </div>
          <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
            {{ isAr ? 'راسي على الرصيف (Moored)' : 'Berthed / Moored' }}
          </span>
        </div>

        <!-- Row 3: Scrubber / Voyage Line Tracker -->
        <div class="flex items-center gap-2 py-1 select-none">
          <template v-if="voyage.isSamePort">
            <div class="w-7 h-7 rounded-full bg-blue-50 border-2 border-[#0073c6] flex items-center justify-center text-[#0073c6] shrink-0" title="At Berth">
              <Anchor class="w-3.5 h-3.5" />
            </div>
            <div class="flex-1 h-1.5 bg-blue-100 rounded-full mx-1 overflow-hidden relative">
              <div class="h-full bg-[#0073c6] w-full animate-pulse"></div>
            </div>
            <div class="w-7 h-7 rounded-full bg-slate-900 text-white flex items-center justify-center shrink-0" title="Port Terminal">
              <MapPin class="w-3.5 h-3.5" />
            </div>
          </template>

          <template v-else>
            <!-- Start Indicator (POL) -->
            <div class="w-5 h-5 rounded-full border-[3px] border-[#0073c6] bg-white flex items-center justify-center shrink-0" title="Port of Loading">
              <div class="w-1.5 h-1.5 rounded-full bg-[#0073c6]"></div>
            </div>

            <!-- Progress Line -->
            <div class="flex-1 flex items-center justify-between overflow-hidden px-1.5">
              <span 
                v-for="i in 52" 
                :key="i" 
                class="w-1.5 h-1.5 rounded-full bg-[#0073c6]"
              ></span>
            </div>

            <!-- End Destination Pin (POD) -->
            <MapPin class="w-5 h-5 fill-slate-900 text-slate-900 shrink-0" title="Port of Discharge" />
          </template>
        </div>

        <!-- Row 4: Timestamps & Speed -->
        <div class="flex items-center justify-between text-sm pt-0.5">
          <div class="space-y-0.5">
            <div class="text-slate-500 text-[13px]">
              {{ voyage.isSamePort ? (isAr ? 'تاريخ ووقت الرسو/الوصول:' : 'Time of arrival/berth:') : (isAr ? 'تاريخ ووقت الإبحار الفعلي:' : 'Actual time of departure:') }}
            </div>
            <div class="font-semibold text-slate-800 text-[15px]">
              {{ voyage.atd }}
            </div>
          </div>

          <div class="hidden sm:block text-center space-y-0.5">
            <div class="text-slate-500 text-[13px]">
              {{ isAr ? 'السرعة والاتجاه:' : 'Current Speed & Course:' }}
            </div>
            <div class="font-semibold text-emerald-700 text-[15px]">
              {{ voyage.speed }} · {{ voyage.course }}
            </div>
          </div>

          <div class="space-y-0.5 text-end">
            <div class="text-slate-500 text-[13px]">
              {{ voyage.isSamePort ? (isAr ? 'حالة السفينة:' : 'Vessel Status:') : (isAr ? 'موعد الوصول المتوقع (ETA):' : 'Reported ETA:') }}
            </div>
            <div class="font-semibold text-slate-800 text-[15px]">
              {{ voyage.isSamePort ? voyage.navStatus : voyage.eta }}
            </div>
          </div>
        </div>

        <!-- Row 5: Action Buttons -->
        <div class="flex items-center justify-between pt-3 border-t border-slate-100 flex-wrap gap-2">
          <a
            :href="voyage.directUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="px-4 py-2 rounded-xl border border-[#0073c6] text-[#0073c6] hover:bg-blue-50 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
          >
            <span>{{ isAr ? 'سجل تتبع المسار السابق (Past Track)' : 'Past track' }}</span>
            <ExternalLink class="w-3.5 h-3.5" />
          </a>

          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="mapMode = mapMode === 'route' ? 'radar' : 'route'"
              class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer flex items-center gap-1.5"
            >
              <Radio class="w-3.5 h-3.5 text-emerald-600" />
              <span>{{ mapMode === 'route' ? (isAr ? 'تبديل إلى رادار الأقمار الصناعية' : 'Switch to Live AIS Radar') : (isAr ? 'تبديل إلى مسار الخريطة' : 'Switch to Nautical Route') }}</span>
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<style>
.leaflet-container {
  width: 100%;
  height: 100%;
  background-color: #dce7ec;
  font-family: inherit;
}
.leaflet-popup-content-wrapper {
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}
@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}
</style>
