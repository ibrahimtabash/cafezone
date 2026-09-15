// Exercise the actual WebSocket + signed HTTP broadcast without creating orders.
import { readFileSync } from 'node:fs';
import { createHash, createHmac, randomUUID } from 'node:crypto';

const env = Object.fromEntries(readFileSync('.env', 'utf8').split(/\r?\n/).filter(line => /^[A-Z_]+=/.test(line)).map(line => {
    const index = line.indexOf('=');
    return [line.slice(0, index), line.slice(index + 1).replace(/^"|"$/g, '')];
}));
const key = env.REVERB_APP_KEY;
const host = '127.0.0.1:8080';
const channel = `smoke.${randomUUID()}`;
const ws = new WebSocket(`ws://${host}/app/${key}?protocol=7&client=js&version=8.4.0`);
const timeout = setTimeout(() => { console.error('Reverb smoke test timed out'); process.exit(1); }, 12000);
ws.addEventListener('error', () => { console.error('WebSocket connection failed'); process.exit(1); });
ws.addEventListener('message', async ({data}) => {
    const event = JSON.parse(data);
    if (event.event === 'pusher:connection_established') ws.send(JSON.stringify({event:'pusher:subscribe', data:{channel}}));
    if (event.event === 'pusher_internal:subscription_succeeded') {
        const body = JSON.stringify({name:'SmokeTest', channels:[channel], data:JSON.stringify({ok:true})});
        const path = `/apps/${env.REVERB_APP_ID}/events`;
        const query = new URLSearchParams({auth_key:key,auth_timestamp:String(Math.floor(Date.now()/1000)),auth_version:'1.0',body_md5:createHash('md5').update(body).digest('hex')}).toString();
        const signature = createHmac('sha256',env.REVERB_APP_SECRET).update(`POST\n${path}\n${query}`).digest('hex');
        const response = await fetch(`http://${host}${path}?${query}&auth_signature=${signature}`, {method:'POST',headers:{'Content-Type':'application/json'},body});
        if (!response.ok) { console.error(`Broadcast HTTP ${response.status}`); process.exit(1); }
    }
    if (event.event === 'SmokeTest') {
        if (!JSON.parse(event.data).ok) process.exit(1);
        console.log('PASS: subscribed over WebSocket and received a signed Reverb broadcast.');
        clearTimeout(timeout); ws.close();
    }
});
