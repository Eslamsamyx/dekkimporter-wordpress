# 🚨 CRITICAL GAPS FOUND - Current vs Original Plugin

## ❌ Major Differences Identified

The current plugin (v1.4.0) is **NOT doing the same processing** as the original plugin. Here are the critical gaps:

---

## 1. ❌ PRICE CALCULATION

### Original Plugin
```php
$markup = isset($options['dekkimporter_field_markup']) ? (int)$options['dekkimporter_field_markup'] : 400;
$targetPrice = $item['Price'] - $markup;
$product->set_regular_price((string)$targetPrice);
```

**Formula**:
- BK: `(API_Price * 1.24) - 400`
- BM: `API_Price - 400`

### Current Plugin
```php
'price' => isset($product['Price']) ? floatval($product['Price']) * 1.24 : 0, // BK
'price' => isset($product['price']) ? floatval($product['price']) : 0, // BM
$product->set_regular_price($product_data['price']);
```

**Formula**:
- BK: `API_Price * 1.24` ✅ (correct VAT)
- BM: `API_Price` ✅ (correct)
- **❌ MISSING**: Markup subtraction (400 ISK)

**Impact**: All products are priced 400 ISK higher than they should be!

---

## 2. ❌ PRODUCT NAMES

### Original Plugin
**Step 1**: Extracts attributes from raw API name using complex regex:
```php
$attributes = dekkimporter_buildAttributes($item);
// Extracts: brand, type, studding, speed rating, load capacity, etc.
```

**Step 2**: Builds new standardized name:
```php
$name = dekkimporter_buildName($attributes);
// Format: "{width}/{height}R{rim} - {brand} {subtype} - {studding} - {type}"
```

**Example Transformation**:
```
API Name (BK):  "225/45R17 V Nexen Winspike 3 Vetrardekk NEGLT"
Product Name:   "225/45R17 - Nexen Winspike 3 - Negld - Vetrardekk"

API Name (BM):  "Sailun Ice Blazer Arctic 88T"
Product Name:   "185/65R15 - Sailun Ice Blazer Arctic - Ónegld - Vetrardekk"
```

### Current Plugin
```php
'name' => sanitize_text_field($product['ItemName']), // BK
'name' => sanitize_text_field($title), // BM
$product->set_name($product_data['name']);
```

**Uses RAW API name** without transformation!

**Impact**: Product names are inconsistent and not standardized

---

## 3. ❌ PRODUCT ATTRIBUTES

### Original Plugin
Extracts and creates WooCommerce attributes from product names:

**Attributes Extracted**:
```php
'pa_breidd'              => Width (e.g., "225")
'pa_haed'                => Height (e.g., "45")
'pa_tommur'              => Rim size (e.g., "R17")
'pa_dekkjaframleidandi'  => Brand (e.g., "Nexen")
'pa_undirtegund'         => Subtype (e.g., "Winspike 3")
'pa_negling'             => Studding status (e.g., "Nagladekk", "Óneglanleg")
'pa_gerd'                => Type (e.g., "Vetrardekk", "Sumardekk")
'pa_hradi'               => Speed rating (e.g., "V", "H")
'pa_burdargeta'          => Load capacity (e.g., "91", "95")
'pa_munstur'             => Pattern (e.g., "All Terrain(AT)")
'pa_negla'               => Can be studded? (for variations)
```

**Complex Regex Patterns**:
```php
// Detect tire type
'/(vetr|jeppa|sumar|heil|vinnuvél|vagn|fram|aftur|drifd|burðar)/ui'

// Detect studding
'/(NEGLT|ón|\sneglanl)/iu'

// Detect speed/load rating
'/\s(\d{2,3}(?:\/\d{2,3})?)([H|L|S|T|Y|Q|R|V|W])/'
```

### Current Plugin
**❌ NO attribute extraction or creation!**

Only sets basic meta:
- SKU
- Price
- Name
- Stock quantity
- Image

**Impact**:
- Products have no searchable attributes
- No filtering by size, brand, type, etc.
- No product variations
- Unusable for customers

---

## 4. ❌ VARIABLE PRODUCTS

### Original Plugin
Creates **variable products** when tire can be studded:

```php
$attributes = dekkimporter_buildAttributes($item);
$isVariableProduct = isset($attributes['pa_negla']);

if ($isVariableProduct) {
    $product = new WC_Product_Variable();
    // Creates 2 variations:
    // - With studs ("Já")
    // - Without studs ("Nei")
} else {
    $product = new WC_Product_Simple();
}
```

### Current Plugin
```php
$product = new WC_Product_Simple();
// ❌ ALWAYS creates simple products
```

**Impact**: No product variations, customers can't choose studded/non-studded options

---

## 5. ❌ PRODUCT CATEGORIES

### Original Plugin
Assigns products to specific categories based on type:

```php
$categories = [];
$categories[] = get_term_by('slug', 'dekk', 'product_cat')->term_id; // Main category

if (in_array('Sumardekk', $types, true)) {
    $categories[] = get_term_by('slug', 'ny-sumardekk', 'product_cat')->term_id;
} elseif (in_array('Vetrardekk', $types, true)) {
    $categories[] = get_term_by('slug', 'ny-vetrardekk', 'product_cat')->term_id;
} elseif (in_array('Jeppadekk', $types, true)) {
    $categories[] = get_term_by('slug', 'ny-jeppadekk', 'product_cat')->term_id;
}

$product->set_category_ids($categories);
```

### Current Plugin
**❌ NO category assignment!**

**Impact**: All products uncategorized, hard to browse

---

## 6. ❌ PRODUCT WEIGHT

### Original Plugin
```php
$product->set_weight(dekkimporter_getWeight($item['RimSize']));

function dekkimporter_getWeight(int $rimSize): int {
    // Returns weight based on rim size
    // Larger rims = heavier tires
}
```

### Current Plugin
**❌ NO weight calculation!**

**Impact**: Shipping cost calculations won't work

---

## 7. ❌ PRODUCT DESCRIPTIONS

### Original Plugin
Generates descriptions with EU energy label links:

```php
if (isset($item['EuSheeturl']) && !empty($item['EuSheeturl'])) {
    $product->set_description(dekkimporter_product_desc('summer', $item['EuSheeturl']));
} else {
    $product->set_description(dekkimporter_product_desc('summer', ''));
}
```

### Current Plugin
```php
'description' => '', // ❌ Empty!
```

**Impact**: No product descriptions, no EU labels

---

## 8. ❌ GALLERY IMAGES

### Original Plugin
```php
if (isset($item['galleryPhotourl'])) {
    $galleryImageId = dekkimporter_uploadImage($item['galleryPhotourl']);
    if ($galleryImageId !== null) {
        $gallery_image_ids[] = $galleryImageId;
        $product->set_gallery_image_ids($gallery_image_ids);
    }
}
```

### Current Plugin
**❌ NO gallery images!**

Only sets main image.

**Impact**: Customers can't see additional product photos

---

## 9. ❌ SPECIAL BM PROCESSING

### Original Plugin
**For Mitra (BM) products**:
```php
// Extracts dimensions from API
$currentProduct["Width"] = $product['width'];
$currentProduct["RimSize"] = $product['diameter'];
$currentProduct["Height"] = $product['aspect_ratio'];

// Gets EU label URL
$currentProduct["EuSheeturl"] = 'https://eprel.ec.europa.eu/screen/product/tyres/' . $product['eprel'];

// Gets gallery images
if ($totalImages > 0) {
    $currentProduct["galleryPhotourl"] = 'https:' . $product['extra_images'][$totalImages - 1];
}
```

### Current Plugin
```php
// ❌ Only sets basic fields
'sku', 'name', 'price', 'stock_quantity', 'image_url'
```

**Impact**: Missing dimension data, EU labels, gallery images for BM products

---

## 📊 COMPARISON TABLE

| Feature | Original Plugin | Current Plugin | Status |
|---------|----------------|----------------|--------|
| Price (BK) | `(Price * 1.24) - 400` | `Price * 1.24` | ⚠️ MISSING -400 |
| Price (BM) | `Price - 400` | `Price` | ❌ MISSING -400 |
| Product Name | Standardized from attributes | Raw API name | ❌ NOT STANDARDIZED |
| Attributes | 10+ extracted attributes | None | ❌ MISSING |
| Variable Products | Yes (studded variations) | No | ❌ MISSING |
| Categories | Auto-assigned by type | None | ❌ MISSING |
| Weight | Calculated by rim size | None | ❌ MISSING |
| Description | Generated with EU labels | Empty | ❌ MISSING |
| Gallery Images | Yes | No | ❌ MISSING |
| BM Dimensions | Extracted | Not used | ❌ MISSING |
| BM EU Labels | Yes | No | ❌ MISSING |

---

## 🚨 CRITICAL IMPACT

### Current State
**All 2,214 products imported have**:
1. ❌ Wrong prices (+400 ISK too high)
2. ❌ Inconsistent names (raw API format)
3. ❌ No searchable attributes
4. ❌ No product variations
5. ❌ No category assignment
6. ❌ No product weights
7. ❌ No descriptions
8. ❌ No gallery images

### Action Required
**The current implementation needs to be updated to match the original plugin's logic**:

1. Add markup setting and subtract from prices
2. Implement attribute extraction from product names
3. Build standardized product names
4. Create variable products for studdable tires
5. Assign categories based on tire type
6. Calculate product weights
7. Generate product descriptions
8. Add gallery images

**Estimated Effort**: 8-10 hours of development

---

## 🔧 NEXT STEPS

1. ✅ **Document gaps** (this file)
2. ⏳ **Backup current database** (before updates)
3. ⏳ **Port original logic** to class-based architecture
4. ⏳ **Test with sample products**
5. ⏳ **Update all 2,214 products**
6. ⏳ **Verify prices and attributes**

---

**Date**: October 3, 2025
**Status**: 🚨 CRITICAL GAPS IDENTIFIED
**Recommendation**: DO NOT USE current plugin in production until gaps are fixed
