{
  "project": {
    "name": "Web Kasir (POS)",
    "description": "Aplikasi kasir berbasis web menggunakan PHP Native dan MySQL",
    "technology": {
      "frontend": [
        "HTML5",
        "CSS3",
        "JavaScript",
        "Bootstrap 5"
      ],
      "backend": "PHP Native",
      "database": "MySQL",
      "payment_gateway": "Midtrans",
      "server": "Apache (XAMPP)"
    }
  },

  "design": {
    "reference": "desain.md",
    "theme": "Modern Minimalist",
    "primary_color": "#4F46E5",
    "secondary_color": "#F3F4F6",
    "font": "Poppins",
    "responsive": true
  },

  "roles": [
    {
      "name": "Admin",
      "permissions": [
        "Kelola pengguna",
        "Kelola produk",
        "Kelola kategori",
        "Melihat laporan",
        "Mengatur metode pembayaran",
        "Mengelola transaksi"
      ]
    },
    {
      "name": "Kasir",
      "permissions": [
        "Melakukan transaksi",
        "Mencetak struk",
        "Melihat riwayat transaksi"
      ]
    }
  ],

  "pages": [
    {
      "name": "Login",
      "path": "/login.php",
      "features": [
        "Input username",
        "Input password",
        "Remember me",
        "Logout"
      ]
    },

    {
      "name": "Dashboard Admin",
      "path": "/admin/index.php",
      "features": [
        "Total produk",
        "Total transaksi",
        "Pendapatan harian",
        "Grafik penjualan"
      ]
    },

    {
      "name": "Kelola User",
      "path": "/admin/user/",
      "features": [
        "Tambah user",
        "Edit user",
        "Hapus user",
        "Pencarian user"
      ]
    },

    {
      "name": "Kelola Produk",
      "path": "/admin/produk/",
      "features": [
        "CRUD produk",
        "Upload gambar",
        "Stok barang",
        "Kategori produk"
      ]
    },

    {
      "name": "Kelola Kategori",
      "path": "/admin/kategori/",
      "features": [
        "Tambah kategori",
        "Edit kategori",
        "Hapus kategori"
      ]
    },

    {
      "name": "Laporan",
      "path": "/admin/laporan/",
      "features": [
        "Filter tanggal",
        "Cetak PDF",
        "Export Excel"
      ]
    },

    {
      "name": "Dashboard Kasir",
      "path": "/kasir/index.php",
      "features": [
        "Daftar produk",
        "Keranjang belanja",
        "Pencarian produk"
      ]
    },

    {
      "name": "Transaksi",
      "path": "/kasir/transaksi.php",
      "features": [
        "Tambah ke keranjang",
        "Hitung total",
        "Diskon",
        "Pajak"
      ]
    },

    {
      "name": "Pembayaran",
      "path": "/kasir/payment.php",
      "features": [
        "Tunai",
        "QRIS",
        "Transfer Bank",
        "E-wallet"
      ]
    }
  ],

  "payment_gateway": {
    "provider": "Midtrans",
    "mode": "Sandbox",
    "features": [
      "QRIS",
      "GoPay",
      "ShopeePay",
      "Bank Transfer",
      "Credit Card"
    ],
    "integration_steps": [
      "Install Midtrans PHP SDK",
      "Konfigurasi Server Key",
      "Konfigurasi Client Key",
      "Generate Snap Token",
      "Redirect ke halaman pembayaran",
      "Terima callback pembayaran",
      "Update status transaksi"
    ]
  },

  "database": {
    "tables": [
      "users",
      "produk",
      "kategori",
      "transaksi",
      "detail_transaksi",
      "payments"
    ]
  },

  "folder_structure": {
    "config": [
      "koneksi.php",
      "auth.php"
    ],

    "admin": [
      "index.php",
      "produk/",
      "kategori/",
      "user/",
      "laporan/"
    ],

    "kasir": [
      "index.php",
      "transaksi.php",
      "payment.php"
    ],

    "assets": [
      "css/",
      "js/",
      "images/"
    ]
  },

  "development_phases": [
    {
      "phase": 1,
      "name": "Persiapan",
      "tasks": [
        "Instalasi XAMPP",
        "Membuat database",
        "Membuat struktur folder"
      ]
    },

    {
      "phase": 2,
      "name": "Autentikasi",
      "tasks": [
        "Halaman login",
        "Session",
        "Logout",
        "Hak akses admin dan kasir"
      ]
    },

    {
      "phase": 3,
      "name": "CRUD Master Data",
      "tasks": [
        "Produk",
        "Kategori",
        "User"
      ]
    },

    {
      "phase": 4,
      "name": "Transaksi Kasir",
      "tasks": [
        "Keranjang",
        "Perhitungan total",
        "Cetak struk"
      ]
    },

    {
      "phase": 5,
      "name": "Payment Gateway",
      "tasks": [
        "Integrasi Midtrans",
        "Callback pembayaran",
        "Status transaksi"
      ]
    },

    {
      "phase": 6,
      "name": "Laporan",
      "tasks": [
        "Laporan harian",
        "Laporan bulanan",
        "Export PDF",
        "Export Excel"
      ]
    }
  ]
}