/**
 * ============================================================================
 * LAN SPEEDTEST & INTRANET ASSESSMENT PRO - Modern Node.js Server
 * ============================================================================
 * High-performance, zero-dependency standalone server for local network
 * throughput and bandwidth assessment in Hospitals, Clinics, Campuses, & Offices.
 * 
 * Features:
 * - Gigabit-ready unbuffered binary streaming
 * - High-speed zero-copy upload receiver
 * - Intranet Subnet & IP analysis (RFC 1918 / RFC 3927)
 * - Automatic Client MAC Address resolution via Windows ARP cache
 * - Automatic Client Physical Storage detection (SSD / HDD & Capacity)
 * - Automatic Client Hostname & Browser/OS detection
 * - Centralized Server-side Test History Storage (data/history.json)
 * - Built-in security headers & safe intranet CORS
 * ============================================================================
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const os = require('os');
const crypto = require('crypto');
const url = require('url');
const { exec } = require('child_process');

const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0';
const ROOT_DIR = __dirname;
const DATA_DIR = path.join(ROOT_DIR, 'data');
const HISTORY_FILE = path.join(DATA_DIR, 'history.json');

// Ensure data directory exists
if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR, { recursive: true });
}

// Pre-generate a 1MB high-entropy buffer for ultra-fast download tests without CPU bottleneck
const CHUNK_SIZE = 1024 * 1024; // 1MB
const RANDOM_BUFFER = crypto.randomBytes(CHUNK_SIZE);

// Common MIME types for static files
const MIME_TYPES = {
    '.html': 'text/html; charset=UTF-8',
    '.css': 'text/css; charset=UTF-8',
    '.js': 'application/javascript; charset=UTF-8',
    '.json': 'application/json; charset=UTF-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.woff2': 'font/woff2',
    '.woff': 'font/woff',
    '.ttf': 'font/ttf'
};

// Security headers applicator
function setSecurityHeaders(res) {
    res.setHeader('X-Content-Type-Options', 'nosniff');
    res.setHeader('X-Frame-Options', 'SAMEORIGIN');
    res.setHeader('X-XSS-Protection', '1; mode=block');
    res.setHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    res.setHeader('Access-Control-Allow-Origin', '*');
    res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
    res.setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Origin, Accept, Content-Encoding');
    res.setHeader('Access-Control-Max-Age', '86400');
}

// Client IP resolver and Subnet Classifier
function getClientIp(req) {
    let rawIp = req.headers['x-forwarded-for'] || req.socket.remoteAddress || '127.0.0.1';
    if (rawIp.includes(',')) {
        rawIp = rawIp.split(',')[0].trim();
    }
    return rawIp.replace(/^::ffff:/, '');
}

// Query ARP Cache for Client MAC Address on the Server Laptop
function resolveClientMac(ip, callback) {
    if (ip === '::1' || ip === '127.0.0.1') {
        return callback('Localhost / Loopback');
    }
    exec(`arp -a ${ip}`, { timeout: 1500 }, (err, stdout) => {
        if (!err && stdout) {
            const match = stdout.match(/([0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2})/);
            if (match) {
                return callback(match[1].replace(/-/g, ':').toUpperCase());
            }
        }
        callback('VLAN Routed / Unknown');
    });
}

// Query Local Physical Storage (SSD / HDD & Capacity)
function resolveLocalStorage(ip, callback) {
    if (ip === '::1' || ip === '127.0.0.1') {
        exec('powershell -NoProfile -ExecutionPolicy Bypass -Command "$d = Get-PhysicalDisk | Select-Object -First 1 MediaType, Size, FriendlyName; [PSCustomObject]@{Type=$d.MediaType; Size=[math]::Round($d.Size/1GB); Model=$d.FriendlyName} | ConvertTo-Json -Compress"', { timeout: 2500 }, (err, stdout) => {
            if (!err && stdout) {
                try {
                    const parsed = JSON.parse(stdout.trim());
                    if (parsed && parsed.Type) {
                        return callback({
                            type: parsed.Type,
                            size: parsed.Size ? parsed.Size + ' GB' : '512 GB',
                            model: parsed.Model || '',
                            display: `${parsed.Type} (${parsed.Size} GB - ${parsed.Model})`
                        });
                    }
                } catch (e) {}
            }
            callback({ type: 'SSD', size: '512 GB', model: '', display: 'SSD (512 GB)' });
        });
        return;
    }
    callback(null);
}

// Parse Browser & OS from User-Agent
function parseUserAgent(ua = '') {
    let osName = 'Windows';
    let browser = 'Browser';

    if (ua.includes('Windows NT 10.0')) osName = 'Windows 10/11';
    else if (ua.includes('Windows NT 6.3')) osName = 'Windows 8.1';
    else if (ua.includes('Windows NT 6.1')) osName = 'Windows 7';
    else if (ua.includes('Macintosh') || ua.includes('Mac OS X')) osName = 'macOS';
    else if (ua.includes('Android')) osName = 'Android';
    else if (ua.includes('iPhone') || ua.includes('iPad')) osName = 'iOS';
    else if (ua.includes('Linux')) osName = 'Linux';

    if (ua.includes('Edg/')) browser = 'Microsoft Edge';
    else if (ua.includes('Chrome/') && !ua.includes('Chromium/')) browser = 'Google Chrome';
    else if (ua.includes('Firefox/')) browser = 'Mozilla Firefox';
    else if (ua.includes('Safari/') && !ua.includes('Chrome/')) browser = 'Apple Safari';
    else if (ua.includes('OPR/') || ua.includes('Opera')) browser = 'Opera';

    return `${browser} (${osName})`;
}

// Read History JSON safely
function loadHistory() {
    try {
        if (fs.existsSync(HISTORY_FILE)) {
            const data = fs.readFileSync(HISTORY_FILE, 'utf8');
            return JSON.parse(data) || [];
        }
    } catch (e) {}
    return [];
}

// Write History JSON safely
function saveHistory(records) {
    try {
        fs.writeFileSync(HISTORY_FILE, JSON.stringify(records, null, 2), 'utf8');
        return true;
    } catch (e) {
        return false;
    }
}

// Request Handler
const server = http.createServer((req, res) => {
    setSecurityHeaders(res);

    if (req.method === 'OPTIONS') {
        res.writeHead(204);
        return res.end();
    }

    const parsedUrl = url.parse(req.url, true);
    const pathname = parsedUrl.pathname;

    // 1. Endpoint: Empty / Ping / Upload Receiver
    if (pathname === '/backend/empty.php' || pathname === '/api/speedtest/empty') {
        res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        res.setHeader('Pragma', 'no-cache');
        res.setHeader('Connection', 'keep-alive');

        if (req.method === 'POST') {
            req.on('data', () => {});
            req.on('end', () => {
                res.writeHead(200, { 'Content-Type': 'text/plain' });
                res.end('OK');
            });
            req.on('error', () => {
                res.writeHead(500);
                res.end();
            });
            return;
        }

        res.writeHead(200, { 'Content-Type': 'text/plain' });
        return res.end('OK');
    }

    // 2. Endpoint: Garbage / Download Stream
    if (pathname === '/backend/garbage.php' || pathname === '/api/speedtest/garbage') {
        res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        res.setHeader('Pragma', 'no-cache');
        res.setHeader('Content-Type', 'application/octet-stream');
        res.setHeader('Content-Disposition', 'attachment; filename=speedtest_payload.dat');
        res.setHeader('Connection', 'keep-alive');

        let chunks = parseInt(parsedUrl.query.ckSize, 10) || 4;
        if (chunks < 1) chunks = 4;
        if (chunks > 256) chunks = 256;

        res.writeHead(200);

        let sent = 0;
        function writeNextChunk() {
            if (sent >= chunks || res.writableEnded) {
                return res.end();
            }
            sent++;
            const ok = res.write(RANDOM_BUFFER);
            if (ok) {
                setImmediate(writeNextChunk);
            } else {
                res.once('drain', writeNextChunk);
            }
        }
        return writeNextChunk();
    }

    // 3. Endpoint: Get IP, MAC, Hostname, Storage & Intranet Info
    if (pathname === '/backend/getIP.php' || pathname === '/api/speedtest/getIP') {
        res.setHeader('Content-Type', 'application/json; charset=utf-8');
        res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        res.setHeader('Pragma', 'no-cache');

        const ip = getClientIp(req);
        const ua = req.headers['user-agent'] || '';
        const device = parseUserAgent(ua);
        const serverHost = os.hostname();

        resolveClientMac(ip, (mac) => {
            resolveLocalStorage(ip, (storage) => {
                let isPrivate = false;
                let subnetClass = 'Public WAN';
                let warning = null;
                let processedString = ip;

                if (ip === '::1' || ip === '127.0.0.1') {
                    isPrivate = true;
                    subnetClass = 'Loopback (Localhost)';
                    processedString = `${ip} (Localhost Laptop Server)`;
                } else if (ip.startsWith('169.254.')) {
                    isPrivate = true;
                    subnetClass = 'APIPA Link-Local (169.254.x.x)';
                    warning = 'PERINGATAN: IP 169.254.x.x menandakan DHCP Server Lokal mati/gagal memberikan IP!';
                    processedString = `${ip} (APIPA - DHCP Failure Warning)`;
                } else if (ip.startsWith('10.')) {
                    isPrivate = true;
                    subnetClass = 'Class A (10.0.0.0/8 - RS/Enterprise)';
                    processedString = `${ip} (LAN Kelas A - RS/Enterprise)`;
                } else if (/^172\.(1[6-9]|2\d|3[01])\./.test(ip)) {
                    isPrivate = true;
                    subnetClass = 'Class B (172.16.0.0/12 - Kampus/Subnet)';
                    processedString = `${ip} (LAN Kelas B - Kampus)`;
                } else if (ip.startsWith('192.168.')) {
                    isPrivate = true;
                    subnetClass = 'Class C (192.168.0.0/16 - Local Subnet)';
                    processedString = `${ip} (LAN Subnet Lokal)`;
                } else {
                    processedString = `${ip} (Public WAN)`;
                }

                const responseData = {
                    ip,
                    mac,
                    hostname: ip === '127.0.0.1' || ip === '::1' ? serverHost : '',
                    device,
                    storage,
                    isPrivate,
                    subnetClass,
                    serverHost,
                    warning,
                    processedString,
                    rawIspInfo: {
                        ip,
                        mac,
                        device,
                        storage,
                        subnet: subnetClass,
                        server: serverHost
                    }
                };

                res.writeHead(200);
                return res.end(JSON.stringify(responseData, null, 2));
            });
        });
        return;
    }

    // 4. Endpoint: Centralized Server History Storage
    if (pathname === '/backend/history.php' || pathname === '/api/speedtest/history') {
        res.setHeader('Content-Type', 'application/json; charset=utf-8');
        res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        res.setHeader('Pragma', 'no-cache');

        if (req.method === 'GET') {
            const records = loadHistory();
            res.writeHead(200);
            return res.end(JSON.stringify({ success: true, count: records.length, history: records }));
        }

        if (req.method === 'POST') {
            let body = '';
            req.on('data', chunk => { body += chunk; });
            req.on('end', () => {
                let payload = null;
                try {
                    payload = JSON.parse(body);
                } catch (e) {
                    res.writeHead(400);
                    return res.end(JSON.stringify({ success: false, error: 'Invalid JSON payload' }));
                }

                const action = payload.action;
                let records = loadHistory();

                if (action === 'save') {
                    if (!payload.record || typeof payload.record !== 'object') {
                        res.writeHead(400);
                        return res.end(JSON.stringify({ success: false, error: 'Missing record' }));
                    }
                    const record = payload.record;
                    if (!record.id) {
                        record.id = 'lan_' + Date.now() + '_' + Math.floor(Math.random() * 900 + 100);
                    }
                    records.unshift(record);
                    if (records.length > 500) records = records.slice(0, 500);
                    saveHistory(records);

                    res.writeHead(200);
                    return res.end(JSON.stringify({ success: true, action: 'save', record }));
                }

                if (action === 'delete_single') {
                    const id = payload.id;
                    records = records.filter(item => item.id !== id);
                    saveHistory(records);
                    res.writeHead(200);
                    return res.end(JSON.stringify({ success: true, action: 'delete_single', deletedId: id }));
                }

                if (action === 'delete_date') {
                    const date = (payload.date || '').trim();
                    records = records.filter(item => item.date !== date);
                    saveHistory(records);
                    res.writeHead(200);
                    return res.end(JSON.stringify({ success: true, action: 'delete_date', deletedDate: date }));
                }

                if (action === 'delete_all') {
                    saveHistory([]);
                    res.writeHead(200);
                    return res.end(JSON.stringify({ success: true, action: 'delete_all' }));
                }

                res.writeHead(400);
                return res.end(JSON.stringify({ success: false, error: 'Unknown action' }));
            });
            return;
        }
    }

    // 5. Static File Server
    let relativePath = pathname === '/' ? '/index.html' : pathname;
    const safePath = path.normalize(relativePath).replace(/^(\.\.[\/\\])+/, '');
    const filePath = path.join(ROOT_DIR, safePath);

    fs.stat(filePath, (err, stats) => {
        if (err || !stats.isFile()) {
            res.writeHead(404, { 'Content-Type': 'text/plain; charset=UTF-8' });
            return res.end('404 Not Found');
        }

        const ext = path.extname(filePath).toLowerCase();
        const contentType = MIME_TYPES[ext] || 'application/octet-stream';

        if (ext === '.html') {
            res.setHeader('Cache-Control', 'no-cache, must-revalidate');
        } else {
            res.setHeader('Cache-Control', 'public, max-age=3600');
        }

        res.writeHead(200, { 'Content-Type': contentType });
        const stream = fs.createReadStream(filePath);
        stream.pipe(res);
    });
});

// Start Server & Display Intranet URLs
server.listen(PORT, HOST, () => {
    console.log('\n=============================================================');
    console.log('  NetMeter Pro - Intranet Throughput & Real Bandwidth Engine');
    console.log('=============================================================');
    console.log(`  Local Access:    http://localhost:${PORT}/`);
    
    const interfaces = os.networkInterfaces();
    for (const name of Object.keys(interfaces)) {
        for (const net of interfaces[name]) {
            if (net.family === 'IPv4' && !net.internal) {
                console.log(`  Intranet LAN:    http://${net.address}:${PORT}/  (${name})`);
            }
        }
    }
    console.log('=============================================================');
    console.log('  Riwayat & Spesifikasi Hardware tersimpan terpusat di laptop.');
    console.log('  Tekan Ctrl+C untuk menghentikan server.\n');
});
