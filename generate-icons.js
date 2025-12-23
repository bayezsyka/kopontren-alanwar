const sharp = require("sharp");

// Create a simple green square with a white card icon
const size192 = 192;
const size512 = 512;

async function generateIcons() {
    // Create a simple green background with a white card icon shape
    const svg192 = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 192" width="192" height="192">
        <rect width="192" height="192" fill="#008362" rx="32"/>
        <g fill="none" stroke="#fff" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" transform="translate(32,40)">
            <rect x="0" y="0" width="128" height="80" rx="12"/>
            <line x1="0" y1="24" x2="128" y2="24"/>
            <rect x="16" y="44" width="16" height="12" rx="2"/>
            <rect x="48" y="44" width="16" height="12" rx="2"/>
        </g>
    </svg>`;

    const svg512 = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
        <rect width="512" height="512" fill="#008362" rx="80"/>
        <g fill="none" stroke="#fff" stroke-width="20" stroke-linecap="round" stroke-linejoin="round" transform="translate(80,120)">
            <rect x="0" y="0" width="352" height="220" rx="32"/>
            <line x1="0" y1="64" x2="352" y2="64"/>
            <rect x="40" y="120" width="48" height="32" rx="6"/>
            <rect x="120" y="120" width="48" height="32" rx="6"/>
        </g>
    </svg>`;

    await sharp(Buffer.from(svg192))
        .png()
        .toFile("./public/icons/icon-192.png");

    await sharp(Buffer.from(svg512))
        .png()
        .toFile("./public/icons/icon-512.png");

    console.log("Icons generated successfully!");
}

generateIcons().catch(console.error);
