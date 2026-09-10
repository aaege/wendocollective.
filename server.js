const express = require('express');
const path = require('path');
const fs = require('fs');
const multer = require('multer');

const app = express();
const PORT = 3000;

// Setup file upload handling with Multer
const uploadDir = path.join(__dirname, 'public/uploads');
if (!fs.existsSync(uploadDir)) {
  fs.mkdirSync(uploadDir, { recursive: true });
}

const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, uploadDir);
  },
  filename: function (req, file, cb) {
    try {
      const orig = file && file.originalname ? String(file.originalname) : 'photo.jpg';
      const rawExt = path.extname(orig) || '.jpg';
      const ext = rawExt.toLowerCase().replace(/[^a-z0-9.]/g, '') || '.jpg';
      const base = path.basename(orig, rawExt).replace(/[^a-zA-Z0-9_-]/g, '');
      const cleanName = (base.length > 0 ? base.slice(0, 25) : 'item');
      const filename = `prod-${Date.now()}-${cleanName}${ext}`;
      cb(null, filename);
    } catch (e) {
      cb(null, `prod-${Date.now()}-${Math.random().toString(36).slice(2, 8)}.jpg`);
    }
  }
});

const upload = multer({
  storage: storage,
  limits: { 
    fileSize: 30 * 1024 * 1024, // 30MB
    fieldSize: 30 * 1024 * 1024, // 30MB - prevents "Field value too long" on base64 images
    fields: 100
  }
});

// Helper to save base64 data URL images as permanent upload files
function saveBase64Image(dataUrl) {
  try {
    const matches = dataUrl.match(/^data:image\/([a-zA-Z0-9+]+);base64,(.+)$/);
    if (!matches) return dataUrl;
    const rawExt = matches[1].toLowerCase();
    const ext = rawExt === 'jpeg' ? '.jpg' : `.${rawExt.replace(/[^a-z0-9]/g, '')}`;
    const buffer = Buffer.from(matches[2], 'base64');
    const filename = `prod-${Date.now()}-${Math.random().toString(36).slice(2, 8)}${ext}`;
    const filePath = path.join(uploadDir, filename);
    fs.writeFileSync(filePath, buffer);
    return '/uploads/' + filename;
  } catch (e) {
    console.error('Failed to save base64 image:', e.message);
    return dataUrl;
  }
}

// Setup view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Middlewares with expanded limits for high-res images and base64 forms
app.use(express.urlencoded({ extended: true, limit: '32mb' }));
app.use(express.json({ limit: '32mb' }));

// Serve static assets
app.use('/uploads', express.static(uploadDir));
app.use('/public/uploads', express.static(uploadDir));
app.use('/assets', express.static(path.join(__dirname, 'public/assets')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use(express.static(path.join(__dirname, 'public')));

// Products persistence
const DATA_FILE = path.join(__dirname, 'data/products.json');

const initialProducts = [
  {
    id: 1,
    name: 'Ambũi Tee',
    category: 'Drop 01 · Apparel',
    description: '100% heavy cotton, custom screen print with the Ambũi striped motif on the back.',
    price_kes: 2500,
    is_available: 1,
    sizes: 'S, M, L, XL, XXL',
    image_path: '/assets/merch-tee.jpg',
    sort_order: 1
  },
  {
    id: 2,
    name: 'Heritage Hoodie',
    category: 'Drop 01 · Apparel',
    description: '380gsm brushed fleece in deep burgundy with gold embroidered 2.0 stamp on sleeve.',
    price_kes: 4500,
    is_available: 1,
    sizes: 'M, L, XL',
    image_path: '/assets/merch-hoodie.jpg',
    sort_order: 2
  },
  {
    id: 3,
    name: '2.0 Wristband',
    category: 'Drop 01 · Keepsake',
    description: 'Woven fabric collector band with gold locking clasp. Keepsake from edition two.',
    price_kes: 500,
    is_available: 1,
    sizes: 'One Size',
    image_path: '/assets/merch-wristband.jpg',
    sort_order: 3
  }
];

function getProducts() {
  try {
    if (fs.existsSync(DATA_FILE)) {
      const raw = fs.readFileSync(DATA_FILE, 'utf-8');
      const parsed = JSON.parse(raw);
      if (Array.isArray(parsed) && parsed.length > 0) {
        return parsed.map(p => {
          if (!Array.isArray(p.images) || p.images.length === 0) {
            p.images = [p.image_path || '/assets/merch-tee.jpg'];
          }
          if (!p.image_path) {
            p.image_path = p.images[0];
          }
          return p;
        });
      }
    }
  } catch (e) {
    console.error('Failed to read products file:', e.message);
  }
  saveProducts(initialProducts);
  return initialProducts;
}

function saveProducts(list) {
  try {
    const dir = path.dirname(DATA_FILE);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(DATA_FILE, JSON.stringify(list, null, 2), 'utf-8');
  } catch (e) {
    console.error('Failed to write products file:', e.message);
  }
}

let products = getProducts();

// ----------------- MEDIA PERSISTENCE & HELPERS ----------------- //
const MEDIA_FILE = path.join(__dirname, 'data', 'media.json');

const defaultMedia = {
  hero: {
    url: 'https://www.youtube.com/watch?v=cCo4F36en7E',
    video_id: 'cCo4F36en7E',
    title: 'Wendo 2.0 announcement',
    start_seconds: 329,
    poster_url: 'https://img.youtube.com/vi/cCo4F36en7E/hqdefault.jpg',
    caption: 'Playing muted. Tap the speaker on the player to hear it, or watch on'
  },
  short: {
    url: 'https://www.youtube.com/shorts/f3LodYambh8',
    video_id: 'f3LodYambh8',
    title: 'Wendo 2.0 short',
    poster_url: 'https://img.youtube.com/vi/f3LodYambh8/hqdefault.jpg',
    caption: 'First look, also on'
  }
};

function extractYouTubeId(url) {
  if (!url) return '';
  url = String(url).trim();
  if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=|shorts\/)([^#&?]*).*/;
  const match = url.match(regExp);
  if (match && match[2].length === 11) {
    return match[2];
  }
  return '';
}

function extractYouTubeStartTime(url) {
  if (!url) return 0;
  const str = String(url);
  const match = str.match(/[?&]t=([0-9]+)s?/) || str.match(/[?&]start=([0-9]+)/);
  if (match) {
    return parseInt(match[1], 10) || 0;
  }
  return 0;
}

function getMediaSettings() {
  try {
    if (fs.existsSync(MEDIA_FILE)) {
      const raw = fs.readFileSync(MEDIA_FILE, 'utf-8');
      const data = JSON.parse(raw);
      if (data && data.hero) {
        return {
          hero: { ...defaultMedia.hero, ...data.hero },
          short: { ...defaultMedia.short, ...(data.short || {}) }
        };
      }
    }
  } catch (e) {
    console.error('Failed to read media settings:', e.message);
  }
  saveMediaSettings(defaultMedia);
  return defaultMedia;
}

function saveMediaSettings(data) {
  try {
    const dir = path.dirname(MEDIA_FILE);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(MEDIA_FILE, JSON.stringify(data, null, 2), 'utf-8');
  } catch (e) {
    console.error('Failed to write media settings:', e.message);
  }
}

let mediaSettings = getMediaSettings();

const REVIEWS_FILE = path.join(__dirname, 'data/reviews.json');
const defaultReviews = [
  {
    id: 1,
    reviewer_name: 'Wanjiku M.',
    caption: '“The vibe at 1.0 was unmatched. Can’t wait for Ambũi!”',
    video_url: 'https://www.youtube.com/watch?v=cCo4F36en7E',
    poster_url: 'https://img.youtube.com/vi/cCo4F36en7E/hqdefault.jpg',
    video_path: '',
    is_published: 1,
    sort_order: 1
  },
  {
    id: 2,
    reviewer_name: 'Kevin K.',
    caption: '“The food, the culture, the energy. Counting down the days!”',
    video_url: 'https://www.youtube.com/watch?v=f3LodYambh8',
    poster_url: 'https://img.youtube.com/vi/f3LodYambh8/hqdefault.jpg',
    video_path: '',
    is_published: 1,
    sort_order: 2
  }
];

function getReviews() {
  try {
    if (fs.existsSync(REVIEWS_FILE)) {
      const raw = fs.readFileSync(REVIEWS_FILE, 'utf-8');
      const data = JSON.parse(raw);
      if (Array.isArray(data) && data.length > 0) {
        return data;
      }
    }
  } catch (e) {
    console.error('Failed to read reviews.json:', e.message);
  }
  return defaultReviews;
}

function saveReviews(reviewsList) {
  try {
    const dir = path.dirname(REVIEWS_FILE);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(REVIEWS_FILE, JSON.stringify(reviewsList, null, 2), 'utf-8');
  } catch (e) {
    console.error('Failed to write reviews.json:', e.message);
  }
}

let reviews = getReviews();

const orders = [];

// Helper functions matching original PHP helpers
function formatPrice(amount) {
  const num = Number(amount) || 0;
  return 'KES ' + num.toLocaleString();
}

function youtubeEmbedUrl(url) {
  if (!url) return '';
  const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=|shorts\/)([^#&?]*).*/;
  const match = url.match(regExp);
  if (match && match[2].length === 11) {
    return 'https://www.youtube-nocookie.com/embed/' + match[2];
  }
  return '';
}

// ----------------- ROUTES ----------------- //

// Health check endpoint
app.get('/api/health', (req, res) => {
  res.json({ status: 'ok', time: new Date().toISOString() });
});

// JSON API
app.get('/api/products', (req, res) => {
  res.json(products);
});

app.get('/api/reviews', (req, res) => {
  const currentReviews = getReviews();
  res.json(currentReviews.filter(r => r.is_published));
});

// Main landing page (Wendo 2.0)
app.get('/', (req, res) => {
  try {
    const currentReviews = getReviews();
    res.render('index', {
      products: products.sort((a, b) => a.sort_order - b.sort_order),
      reviews: currentReviews.filter(r => r.is_published).sort((a, b) => a.sort_order - b.sort_order),
      media: mediaSettings,
      formatPrice,
      youtubeEmbedUrl
    });
  } catch (err) {
    console.error('Error rendering homepage:', err);
    res.redirect('/');
  }
});

app.get('/index.php', (req, res) => {
  res.redirect('/');
});

// Media JSON API
app.get('/api/media', (req, res) => {
  res.json(mediaSettings);
});

// Recap page (Wendo 1.0)
app.get('/recap.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'public/recap.html'));
});

// Pesapal checkout flow (redirects to homepage on invalid product or error)
app.get('/pesapal/initiate-payment.php', (req, res) => {
  try {
    const productId = parseInt(req.query.product_id, 10);
    const product = products.find(p => p.id === productId);
    if (!product) {
      return res.redirect('/');
    }
    res.render('checkout', {
      product,
      formatPrice
    });
  } catch (e) {
    res.redirect('/');
  }
});

app.post('/pesapal/process-order', (req, res) => {
  const { product_id, size, quantity, customer_name, customer_email, customer_phone, delivery_address } = req.body;
  const product = products.find(p => p.id === parseInt(product_id, 10)) || products[0];
  const qty = parseInt(quantity, 10) || 1;
  const amount = (product.price_kes || 0) * qty;

  const order = {
    order_id: 'WND-' + Math.floor(100000 + Math.random() * 900000),
    product_id: product.id,
    product_name: product.name,
    size: size || 'One Size',
    quantity: qty,
    amount_kes: amount,
    customer_name: customer_name || 'Anonymous Guest',
    customer_email: customer_email || '',
    customer_phone: customer_phone || '',
    delivery_address: delivery_address || 'Kentmere Club Collection',
    payment_status: 'COMPLETED (Sandbox Test)',
    created_at: new Date()
  };

  orders.unshift(order);

  res.render('order-success', {
    order,
    formatPrice
  });
});

// Single product API
app.get('/api/products/:id', (req, res) => {
  const prodId = parseInt(req.params.id, 10);
  const prod = products.find(p => p.id === prodId);
  if (!prod) {
    return res.status(404).json({ error: 'Product not found' });
  }
  res.json(prod);
});

// Admin CMS & installation routes
app.get(['/admin', '/admin/dashboard.php'], (req, res) => {
  res.render('admin', {
    products,
    reviews: getReviews(),
    orders,
    media: mediaSettings,
    formatPrice,
    editId: req.query.edit || '',
    saved: req.query.saved || false,
    media_saved: req.query.media_saved || false,
    deleted: req.query.deleted || false,
    error: req.query.error || ''
  });
});

// Update Media Playing & Reviews (Unified Media Control Panel)
app.post('/admin/media/save', (req, res) => {
  try {
    const {
      hero_url, hero_title, hero_start, hero_poster, hero_caption,
      short_url, short_title, short_poster, short_caption,
      reviews_json
    } = req.body;

    const heroId = extractYouTubeId(hero_url) || mediaSettings.hero.video_id;
    const heroStartSeconds = (hero_start !== undefined && hero_start !== '') ? parseInt(hero_start, 10) : extractYouTubeStartTime(hero_url);
    const heroPoster = (hero_poster && hero_poster.trim()) ? hero_poster.trim() : `https://img.youtube.com/vi/${heroId}/hqdefault.jpg`;

    const shortId = extractYouTubeId(short_url) || mediaSettings.short.video_id;
    const shortPoster = (short_poster && short_poster.trim()) ? short_poster.trim() : `https://img.youtube.com/vi/${shortId}/hqdefault.jpg`;

    mediaSettings = {
      hero: {
        url: hero_url ? hero_url.trim() : mediaSettings.hero.url,
        video_id: heroId,
        title: (hero_title !== undefined && hero_title.trim()) ? hero_title.trim() : mediaSettings.hero.title,
        start_seconds: isNaN(heroStartSeconds) ? 0 : heroStartSeconds,
        poster_url: heroPoster,
        caption: (hero_caption !== undefined && hero_caption.trim()) ? hero_caption.trim() : mediaSettings.hero.caption
      },
      short: {
        url: short_url ? short_url.trim() : mediaSettings.short.url,
        video_id: shortId,
        title: (short_title !== undefined && short_title.trim()) ? short_title.trim() : mediaSettings.short.title,
        poster_url: shortPoster,
        caption: (short_caption !== undefined && short_caption.trim()) ? short_caption.trim() : mediaSettings.short.caption
      }
    };

    saveMediaSettings(mediaSettings);

    // Save reviews if provided in body
    if (reviews_json) {
      try {
        const parsed = typeof reviews_json === 'string' ? JSON.parse(reviews_json) : reviews_json;
        if (Array.isArray(parsed)) {
          reviews = parsed.map((r, i) => {
            const vidUrl = r.video_url ? String(r.video_url).trim() : '';
            const vidId = extractYouTubeId(vidUrl);
            const poster = (r.poster_url && r.poster_url.trim()) 
              ? r.poster_url.trim() 
              : (vidId ? `https://img.youtube.com/vi/${vidId}/hqdefault.jpg` : '');
            return {
              id: Number(r.id) || (i + 1),
              reviewer_name: r.reviewer_name ? String(r.reviewer_name).trim() : `Reviewer ${i + 1}`,
              caption: r.caption ? String(r.caption).trim() : '',
              video_url: vidUrl,
              poster_url: poster,
              video_path: r.video_path || '',
              is_published: (r.is_published === 1 || r.is_published === '1' || r.is_published === true) ? 1 : 0,
              sort_order: Number(r.sort_order) || (i + 1)
            };
          });
          saveReviews(reviews);
        }
      } catch (err) {
        console.error('Error parsing reviews_json:', err.message);
      }
    } else if (Array.isArray(req.body.reviews)) {
      reviews = req.body.reviews.map((r, i) => {
        const vidUrl = r.video_url ? String(r.video_url).trim() : '';
        const vidId = extractYouTubeId(vidUrl);
        const poster = (r.poster_url && r.poster_url.trim()) 
          ? r.poster_url.trim() 
          : (vidId ? `https://img.youtube.com/vi/${vidId}/hqdefault.jpg` : '');
        return {
          id: Number(r.id) || (i + 1),
          reviewer_name: r.reviewer_name ? String(r.reviewer_name).trim() : `Reviewer ${i + 1}`,
          caption: r.caption ? String(r.caption).trim() : '',
          video_url: vidUrl,
          poster_url: poster,
          video_path: r.video_path || '',
          is_published: (r.is_published === 1 || r.is_published === '1' || r.is_published === true) ? 1 : 0,
          sort_order: Number(r.sort_order) || (i + 1)
        };
      });
      saveReviews(reviews);
    }

    if (req.xhr || (req.headers.accept && req.headers.accept.includes('application/json')) || req.is('json')) {
      return res.json({ success: true, media: mediaSettings, reviews });
    }
    return res.redirect('/admin/dashboard.php?media_saved=1#media-card');
  } catch (err) {
    console.error('Error saving media settings:', err.message);
    if (req.xhr || (req.headers.accept && req.headers.accept.includes('application/json'))) {
      return res.status(500).json({ success: false, error: err.message });
    }
    return res.redirect('/admin/dashboard.php?error=' + encodeURIComponent('Failed to update media settings'));
  }
});

// Async Image Upload endpoint for product modal (accepts single or multiple files, any field name)
app.post(['/admin/api/upload-image', '/api/upload-image', '/admin/upload-image'], (req, res) => {
  upload.any()(req, res, (err) => {
    if (err) {
      console.error('Upload multer error:', err.message);
      return res.status(400).json({ success: false, error: err.message || 'Image upload failed' });
    }
    const uploadedFiles = req.files || [];
    if (req.file) uploadedFiles.push(req.file);

    if (uploadedFiles.length === 0) {
      return res.status(400).json({ success: false, error: 'No image file received' });
    }

    const first = uploadedFiles[0];
    const imageUrl = '/uploads/' + first.filename;
    const allUrls = uploadedFiles.map(f => '/uploads/' + f.filename);

    return res.json({
      success: true,
      url: imageUrl,
      urls: allUrls,
      filename: first.filename
    });
  });
});

// Save or Update Product (with image upload, arrangement/main selection, and deletion)
app.post('/admin/products/save', (req, res) => {
  upload.any()(req, res, (err) => {
    if (err) {
      console.error('Multer upload error in /admin/products/save:', err.message);
      return res.redirect('/admin/dashboard.php?error=' + encodeURIComponent(err.message || 'Error processing uploaded images'));
    }

    try {
      const { id, name, category, price_kes, description, sizes, is_available, existing_images, image_preset } = req.body;
      const prodId = id ? parseInt(id, 10) : null;
      const price = parseFloat(price_kes) || 0;
      const available = (is_available === '1' || is_available === 'on' || is_available === 1) ? 1 : 0;

      // Parse existing images array (already arranged and with deleted ones removed by client)
      let finalImages = [];
      if (existing_images) {
        try {
          const parsed = JSON.parse(existing_images);
          if (Array.isArray(parsed)) {
            finalImages = parsed.filter(p => typeof p === 'string' && p.trim().length > 0);
          }
        } catch (e) {
          console.error('Failed to parse existing_images:', e.message);
        }
      }

      // Convert any Base64 Data URL images into real upload files
      finalImages = finalImages.map(img => {
        if (typeof img === 'string' && img.startsWith('data:image/')) {
          return saveBase64Image(img);
        }
        return img;
      });

      // If preset image was selected and not already in finalImages
      if (image_preset && image_preset.trim() && !finalImages.includes(image_preset.trim())) {
        finalImages.push(image_preset.trim());
      }

      // If any new files were uploaded with the form submission, append them
      if (req.files && Array.isArray(req.files) && req.files.length > 0) {
        req.files.forEach(file => {
          finalImages.push('/uploads/' + file.filename);
        });
      }

      // Ensure fallback image if none remaining
      if (finalImages.length === 0) {
        finalImages = ['/assets/merch-tee.jpg'];
      }

      // The first image in the arranged array is the Main Cover Image
      const mainImage = finalImages[0];

      if (prodId) {
        const existing = products.find(p => p.id === prodId);
        if (existing) {
          existing.name = (name || '').trim() || existing.name;
          existing.category = (category || '').trim() || existing.category;
          existing.price_kes = price;
          existing.description = (description || '').trim();
          existing.sizes = (sizes || '').trim();
          existing.is_available = available;
          existing.image_path = mainImage;
          existing.images = finalImages;
        }
      } else {
        const nextId = products.length > 0 ? Math.max(...products.map(p => p.id)) + 1 : 1;
        const newProduct = {
          id: nextId,
          name: (name || 'New Wendo Item').trim(),
          category: (category || 'Drop 01 · Apparel').trim(),
          price_kes: price,
          description: (description || '').trim(),
          sizes: (sizes || 'One Size').trim(),
          is_available: available,
          image_path: mainImage,
          images: finalImages,
          sort_order: products.length + 1
        };
        products.push(newProduct);
      }

      saveProducts(products);
      res.redirect('/admin/dashboard.php?saved=1');
    } catch (saveErr) {
      console.error('Product save error:', saveErr);
      res.redirect('/admin/dashboard.php?error=' + encodeURIComponent('Could not save product: ' + saveErr.message));
    }
  });
});

// Delete Product
app.post('/admin/products/delete', (req, res) => {
  const id = parseInt(req.body.id, 10);
  products = products.filter(p => p.id !== id);
  saveProducts(products);
  res.redirect('/admin/dashboard.php?deleted=1');
});

app.post('/admin/toggle-product', (req, res) => {
  const id = parseInt(req.body.id, 10);
  const prod = products.find(p => p.id === id);
  if (prod) {
    prod.is_available = prod.is_available ? 0 : 1;
    saveProducts(products);
  }
  res.redirect('/admin/dashboard.php');
});

app.get('/install.php', (req, res) => {
  res.render('install');
});

// Error Handler - If there is an error on the page redirect to homepage
app.use((err, req, res, next) => {
  console.error('Handled application error:', err.message || err);
  if (req.path.startsWith('/admin/api') || req.path.startsWith('/api') || req.xhr || (req.headers.accept && req.headers.accept.includes('application/json'))) {
    return res.status(err.status || 500).json({
      success: false,
      error: err.message || 'An unexpected error occurred'
    });
  }
  // If there is an error on the page, redirect to homepage
  res.redirect('/');
});

// 404 Handler - redirect any unknown page route to homepage
app.use((req, res) => {
  if (req.path.startsWith('/admin/api') || req.path.startsWith('/api') || req.xhr || (req.headers.accept && req.headers.accept.includes('application/json'))) {
    return res.status(404).json({ error: 'Endpoint not found' });
  }
  res.redirect('/');
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Wendo Collective server running on http://0.0.0.0:${PORT}`);
});
