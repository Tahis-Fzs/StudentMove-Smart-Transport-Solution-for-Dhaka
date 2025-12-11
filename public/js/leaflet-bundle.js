/*!
 * Ultra-minimal Leaflet stub (map/tileLayer/marker) to avoid CDN and syntax errors.
 * Not a full implementation—sufficient for our page’s marker animations.
 */
(function () {
  const L = {};

  L.map = function (id, opts = {}) {
    const el = typeof id === 'string' ? document.getElementById(id) : id;
    if (!el) return null;
    
    let isDragging = false;
    let dragStart = { x: 0, y: 0 };
    let panOffset = { x: 0, y: 0 };
    
    const map = {
      _el: el,
      _center: opts.center || { lat: 23.8103, lng: 90.4125 },
      _zoom: opts.zoom || 13,
      _layers: [],
      _markers: [],
      setView(center, zoom) {
        if (Array.isArray(center)) {
          this._center = { lat: center[0], lng: center[1] };
        } else if (center) {
          this._center = center;
        }
        this._zoom = zoom || this._zoom;
        panOffset = { x: 0, y: 0 };
        this._updateLayers();
        return this;
      },
      getCenter() {
        return this._center;
      },
      panTo(center) {
        if (Array.isArray(center)) {
          this._center = { lat: center[0], lng: center[1] };
        } else {
          this._center = center;
        }
        panOffset = { x: 0, y: 0 };
        this._updateLayers();
        return this;
      },
      _updateLayers() {
        // Update all layers when map moves
        this._layers.forEach(layer => {
          if (layer && typeof layer._updatePosition === 'function') {
            layer._updatePosition();
          }
        });
        if (this._markers) {
          this._markers.forEach(marker => {
            if (marker && typeof marker._updatePosition === 'function') {
              marker._updatePosition();
            }
          });
        }
      },
      addLayer(layer) {
        if (layer && typeof layer.onAdd === 'function') {
          layer.onAdd(this);
          this._layers.push(layer);
        }
        return this;
      }
    };
    
    // Make map draggable
    el.style.cursor = 'grab';
    el.style.userSelect = 'none';
    
    el.addEventListener('mousedown', (e) => {
      isDragging = true;
      dragStart = { x: e.clientX - panOffset.x, y: e.clientY - panOffset.y };
      el.style.cursor = 'grabbing';
      e.preventDefault();
    });
    
    el.addEventListener('mousemove', (e) => {
      if (isDragging) {
        panOffset.x = e.clientX - dragStart.x;
        panOffset.y = e.clientY - dragStart.y;
        
        // Update center based on pan offset (simplified calculation)
        const latOffset = -panOffset.y / 10000;
        const lngOffset = panOffset.x / 10000;
        map._center.lat = (map._center.lat || 23.8103) + latOffset;
        map._center.lng = (map._center.lng || 90.4125) + lngOffset;
        
        // Move tile layers
        el.querySelectorAll('img').forEach(img => {
          img.style.transform = `translate(${panOffset.x}px, ${panOffset.y}px)`;
        });
        
        map._updateLayers();
      }
    });
    
    el.addEventListener('mouseup', () => {
      isDragging = false;
      el.style.cursor = 'grab';
    });
    
    el.addEventListener('mouseleave', () => {
      isDragging = false;
      el.style.cursor = 'grab';
    });
    
    // Zoom with mouse wheel
    el.addEventListener('wheel', (e) => {
      e.preventDefault();
      const delta = e.deltaY > 0 ? -1 : 1;
      map._zoom = Math.max(1, Math.min(19, map._zoom + delta));
      panOffset = { x: 0, y: 0 };
      map._updateLayers();
    });
    
    return map;
  };

  L.tileLayer = function (url, opts = {}) {
    const tileLayer = {
      _url: url,
      _opts: opts,
      _events: {},
      _map: null,
      on(event, callback) {
        if (!this._events[event]) this._events[event] = [];
        this._events[event].push(callback);
        return this;
      },
      onAdd(map) {
        if (map && map._el) {
          this._map = map;
          // Set dark background for map container
          map._el.style.background = '#1e293b';
          map._el.style.position = 'relative';
          map._el.style.overflow = 'hidden';
          
          this._tileContainer = document.createElement('div');
          this._tileContainer.style.position = 'absolute';
          this._tileContainer.style.top = '0';
          this._tileContainer.style.left = '0';
          this._tileContainer.style.width = '100%';
          this._tileContainer.style.height = '100%';
          map._el.appendChild(this._tileContainer);
          
          // Try to load actual tiles if OpenStreetMap URL
          if (url.includes('openstreetmap.org')) {
            this._loadTiles();
          } else {
            // Fallback: trigger load event immediately
            setTimeout(() => {
              if (this._events.load) this._events.load.forEach(fn => fn());
            }, 100);
          }
        }
      },
      _loadTiles() {
        if (!this._map || !this._tileContainer) return;
        
        // Clear existing tiles
        this._tileContainer.innerHTML = '';
        
        const zoom = this._map._zoom || 13;
        const lat = this._map._center.lat || 23.8103;
        const lng = this._map._center.lng || 90.4125;
        const n = Math.pow(2, zoom);
        const x = Math.floor((lng + 180) / 360 * n);
        const y = Math.floor((1 - Math.log(Math.tan(lat * Math.PI / 180) + 1 / Math.cos(lat * Math.PI / 180)) / Math.PI) / 2 * n);
        
        // Load a 3x3 grid of tiles
        for (let dx = -1; dx <= 1; dx++) {
          for (let dy = -1; dy <= 1; dy++) {
            const img = document.createElement('img');
            img.style.position = 'absolute';
            img.style.width = '256px';
            img.style.height = '256px';
            img.style.left = (dx * 256) + 'px';
            img.style.top = (dy * 256) + 'px';
            img.style.opacity = '0.9';
            
            const tileX = x + dx;
            const tileY = y + dy;
            img.src = this._url.replace('{s}', 'a').replace('{z}', zoom).replace('{x}', tileX).replace('{y}', tileY);
            
            img.onload = () => {
              if (this._events.load && dx === 0 && dy === 0) {
                setTimeout(() => {
                  this._events.load.forEach(fn => fn());
                }, 100);
              }
            };
            img.onerror = () => {
              img.style.display = 'none';
              if (this._events.tileerror && dx === 0 && dy === 0) {
                this._events.tileerror.forEach(fn => fn({ tile: img }));
              }
            };
            
            this._tileContainer.appendChild(img);
          }
        }
      },
      _updatePosition() {
        // Tiles update when map center changes - reload tiles
        if (this._map) {
          this._loadTiles();
        }
      },
      addTo(map) {
        this.onAdd(map);
        return this;
      }
    };
    return tileLayer;
  };

  L.marker = function (latlng, opts = {}) {
    // Normalize latlng to object format
    let normalizedLatLng;
    if (Array.isArray(latlng)) {
      normalizedLatLng = { lat: latlng[0], lng: latlng[1] };
    } else {
      normalizedLatLng = latlng;
    }
    
    const marker = {
      _latlng: normalizedLatLng,
      _map: null,
      _icon: null,
      onAdd(map) {
        this._map = map;
        if (map && map._el) {
          // Create a simple marker icon
          const icon = document.createElement('div');
          icon.style.width = '30px';
          icon.style.height = '30px';
          icon.style.background = '#ef4444';
          icon.style.borderRadius = '50%';
          icon.style.border = '3px solid white';
          icon.style.boxShadow = '0 2px 8px rgba(0,0,0,0.3)';
          icon.style.position = 'absolute';
          icon.style.transform = 'translate(-50%, -50%)';
          icon.style.zIndex = '1000';
          icon.style.cursor = 'pointer';
          icon.style.pointerEvents = 'none'; // Don't interfere with map dragging
          this._icon = icon;
          map._el.appendChild(icon);
          if (!map._markers) map._markers = [];
          map._markers.push(this);
          this._updatePosition();
        }
      },
      _updatePosition() {
        if (this._icon && this._map && this._map._el) {
          // Simple positioning (would need proper projection in real Leaflet)
          const el = this._map._el;
          const centerX = el.offsetWidth / 2;
          const centerY = el.offsetHeight / 2;
          // Approximate positioning based on lat/lng offset from center
          const latOffset = (this._latlng.lat - (this._map._center.lat || 23.8103)) * 10000;
          const lngOffset = (this._latlng.lng - (this._map._center.lng || 90.4125)) * 10000;
          this._icon.style.left = (centerX + lngOffset) + 'px';
          this._icon.style.top = (centerY - latOffset) + 'px';
          this._icon.style.zIndex = '1000';
        }
      },
      addTo(map) {
        this.onAdd(map);
        return this;
      },
      setLatLng(latlng) {
        if (Array.isArray(latlng)) {
          this._latlng = { lat: latlng[0], lng: latlng[1] };
        } else {
          this._latlng = latlng;
        }
        this._updatePosition();
        return this;
      },
      getLatLng() {
        return this._latlng;
      }
    };
    return marker;
  };

  L.DomEvent = {
    on(el, type, fn) { el && el.addEventListener(type, fn, false); return this; },
    off(el, type, fn) { el && el.removeEventListener(type, fn, false); return this; },
    stop(ev) { if (!ev) return; ev.stopPropagation(); ev.preventDefault(); return false; }
  };

  window.L = L;
})();

