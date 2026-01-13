// DB GSM
db = db.getSiblingDB("gsm");

// Index texte produits
db.products.createIndex(
  { label: "text", sku: "text", slug: "text" },
  { name: "products_text" }
);

// Index catégorie (optionnel mais utile)
db.products.createIndex(
  { categoryId: 1 },
  { name: "products_categoryId" }
);

print("✔ MongoDB indexes for GSM created");
