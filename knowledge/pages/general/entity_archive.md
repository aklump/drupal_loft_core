<!--
id: entity_archive
tags: ''
-->

# Archiving Entities

Here is an example of how to use `loft_core_update__archive_entities` in a `hook_update_n` implementation to archive entites.

    /**
     * Create archive of the product entities.
     *
     * @throws \DrupalUpdateException
     */
    function MODULE_update_N() {
      $sql = "SELECT
      created,
      product_id,
      p.revision_id,
      sku,
      title,
      type,
      field_format_value AS format,
      ROUND(commerce_price_amount / 100, 2) AS price,
      field_xml_metadata_xml AS metadata,
      filename AS image_filename,
      uri AS image_uri,
      field_description_references_nid AS related_nid,
      field_product_description_value AS description,
      field_product_overview_value AS overview,
      field_product_contents_value AS contents
    from commerce_product p
      LEFT JOIN field_data_field_product_images pi ON (pi.entity_id = product_id)
      LEFT JOIN field_data_commerce_price cp ON (cp.entity_id = product_id)
      LEFT JOIN field_data_field_format ff ON (ff.entity_id = product_id)
      LEFT JOIN field_data_field_product_description pd ON (pd.entity_id = product_id)
      LEFT JOIN field_data_field_product_contents pc ON (pc.entity_id = product_id)
      LEFT JOIN field_data_field_product_overview po ON (po.entity_id = product_id)
      LEFT JOIN field_data_field_xml_metadata xml ON (xml.entity_id = product_id)
      LEFT JOIN field_data_field_description_references fdr ON (fdr.entity_id = product_id)
      LEFT JOIN file_managed f ON (f.fid = pi.field_product_images_fid)
    WHERE 1;";
      module_load_include('install', 'loft_core', 'loft_core');
    
      return loft_core_update__archive_entities(
        'Create archive of the product entities.',
        $sql,
        'commerce_products',
        [
          ['image_filename', 'image_uri'],
        ],
        function ($key, &$value) {
          if ($key === 'metadata') {
            // Convert XML To JSON.
            $value = simplexml_load_string($value);
            $value = $value ? json_encode($value) : NULL;
          }
    
          return TRUE;
        }
      );
    }
