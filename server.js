const express = require('express');
const path = require('path');

const app = express();
const PORT = 3000;

// Setup view engine
app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'views'));

// Middlewares
app.use(express.urlencoded({ extended: true }));
app.use(express.json());

// Serve static assets
app.use('/assets', express.static(path.join(__dirname, 'public/assets')));
app.use('/assets', express.static(path.join(__dirname, 'assets')));
app.use(express.static(path.join(__dirname, 'public')));

// In-memory data store (persists across requests during runtime)
const products = [
  {
    id: 1,
    name: 'Ambũi Tee',
    category: 'Drop 01 · Apparel',
    description: '100% heavy cotton, custom screen print with the Ambũi striped motif on the back.',
    price_kes: 2500,
    is_available: 1,
    sizes: 'S,M,L,XL,XXL',
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
    sizes: 'M,L,XL',
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
    sizes: '',
    image_path: '/assets/merch-wristband.jpg',
    sort_order: 3
  }
];

const reviews = [
  {
    id: 1,
    reviewer_name: 'Wanjiku M.',
    caption: '“The vibe at 1.0 was unmatched. Can’t wait for Ambũi!”',
    video_url: 'https://www.youtube.com/watch?v=cCo4F36en7E',
    video_path: '',
    is_published: 1,
    sort_order: 1
  },
  {
    id: 2,
    reviewer_name: 'Kevin K.',
    caption: '“The food, the culture, the energy. Counting down the days!”',
    video_url: 'https://www.youtube.com/watch?v=f3LodYambh8',
    video_path: '',
    is_published: 1,
    sort_order: 2
  }
];

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
  res.json(reviews.filter(r => r.is_published));
});

// Main landing page (Wendo 2.0)
app.get('/', (req, res) => {
  res.render('index', {
    products: products.sort((a, b) => a.sort_order - b.sort_order),
    reviews: reviews.filter(r => r.is_published).sort((a, b) => a.sort_order - b.sort_order),
    formatPrice,
    youtubeEmbedUrl
  });
});

app.get('/index.php', (req, res) => {
  res.redirect('/');
});

// Recap page (Wendo 1.0)
app.get('/recap.html', (req, res) => {
  res.sendFile(path.join(__dirname, 'public/recap.html'));
});

// Pesapal checkout flow
app.get('/pesapal/initiate-payment.php', (req, res) => {
  const productId = parseInt(req.query.product_id, 10);
  const product = products.find(p => p.id === productId) || products[0];

  res.render('checkout', {
    product,
    formatPrice
  });
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

// Admin CMS & installation routes
app.get(['/admin', '/admin/dashboard.php'], (req, res) => {
  res.render('admin', {
    products,
    reviews,
    orders,
    formatPrice
  });
});

app.post('/admin/toggle-product', (req, res) => {
  const id = parseInt(req.body.id, 10);
  const prod = products.find(p => p.id === id);
  if (prod) {
    prod.is_available = prod.is_available ? 0 : 1;
  }
  res.redirect('/admin/dashboard.php');
});

app.get('/install.php', (req, res) => {
  res.render('install');
});

// Start server
app.listen(PORT, '0.0.0.0', () => {
  console.log(`Wendo Collective server running on http://0.0.0.0:${PORT}`);
});
