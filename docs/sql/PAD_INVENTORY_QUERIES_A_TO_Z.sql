-- =============================================================================
-- PADMA INVENTORY — Reference queries A to Z
-- Oracle: use LENGTH(TRIM(set_uniq_no)) for non-empty SET codes (CLOB-safe)
-- MySQL: set_uniq_no != '' works on VARCHAR
-- =============================================================================

-- -----------------------------------------------------------------------------
-- A. Active inventory rows (status <> 9)
-- -----------------------------------------------------------------------------
SELECT COUNT(*) AS active_rows
FROM item_inventory
WHERE status <> 9;

-- -----------------------------------------------------------------------------
-- B. Stock positions (packages) — store_type SP
-- -----------------------------------------------------------------------------
SELECT id, set_uniq_no, item_id, branch_id, store_type, status
FROM item_inventory
WHERE store_type = 'SP'
  AND status <> 9
ORDER BY id DESC;

-- -----------------------------------------------------------------------------
-- C. Stock In / Stock Out lines
-- -----------------------------------------------------------------------------
SELECT * FROM item_inventory WHERE store_type = 'SI' AND status <> 9 ORDER BY created_at DESC;
SELECT * FROM item_inventory WHERE store_type = 'SO' AND status <> 9 ORDER BY created_at DESC;

-- -----------------------------------------------------------------------------
-- D. SET_UNIQ_NO list (main items only — exclude spec item_id 17,41,12,14)
-- Oracle non-empty filter:
--   AND set_uniq_no IS NOT NULL AND LENGTH(TRIM(set_uniq_no)) > 0
-- -----------------------------------------------------------------------------
SELECT ii.set_uniq_no,
       MAX(ii.id) AS inventory_id,
       MAX(i.item_title) AS item_title,
       MAX(b.brand_name) AS brand_name
FROM item_inventory ii
LEFT JOIN item_info i ON ii.item_id = i.id
LEFT JOIN brand b ON ii.brand_id = b.id
WHERE ii.status <> 9
  AND ii.item_id NOT IN (17, 41, 12, 14)
  AND ii.set_uniq_no IS NOT NULL
  AND LENGTH(TRIM(ii.set_uniq_no)) > 0   -- Oracle; MySQL: AND ii.set_uniq_no <> ''
GROUP BY ii.set_uniq_no
ORDER BY ii.set_uniq_no;

-- -----------------------------------------------------------------------------
-- E. Barcode module — sets by HR branch (example hr_branch_id = 89 Dhanmondi)
-- Match branch.id and hr_branch_id on item_inventory.branch_id
-- -----------------------------------------------------------------------------
SELECT b.id AS branch_pk, b.hr_branch_id, b.branch_name
FROM branch b
WHERE b.hr_branch_id = 89;

SELECT ii.set_uniq_no, ii.branch_id, i.item_title
FROM item_inventory ii
LEFT JOIN item_info i ON ii.item_id = i.id
WHERE ii.status <> 9
  AND ii.item_id NOT IN (17, 41, 12, 14)
  AND ii.set_uniq_no IS NOT NULL
  AND LENGTH(TRIM(ii.set_uniq_no)) > 0
  AND ii.branch_id IN (
    SELECT id FROM branch WHERE hr_branch_id = 89
    UNION
    SELECT hr_branch_id FROM branch WHERE hr_branch_id = 89
  )
GROUP BY ii.set_uniq_no, ii.branch_id, i.item_title
ORDER BY ii.set_uniq_no;

-- -----------------------------------------------------------------------------
-- F. Division — all branches in division
-- -----------------------------------------------------------------------------
SELECT ii.set_uniq_no, ii.branch_id, br.branch_name
FROM item_inventory ii
JOIN branch br ON ii.branch_id = br.id OR ii.branch_id = br.hr_branch_id
WHERE br.division_id = :division_id
  AND ii.status <> 9
  AND LENGTH(TRIM(ii.set_uniq_no)) > 0;

-- -----------------------------------------------------------------------------
-- G. Employee assignment on inventory
-- -----------------------------------------------------------------------------
SELECT set_uniq_no, to_employee_id, branch_id, condition
FROM item_inventory
WHERE to_employee_id IS NOT NULL
  AND status <> 9;

-- -----------------------------------------------------------------------------
-- H. Transfer audit
-- -----------------------------------------------------------------------------
SELECT * FROM item_transfer_logs ORDER BY transferred_at DESC;

-- -----------------------------------------------------------------------------
-- I. Swap pending / received
-- -----------------------------------------------------------------------------
SELECT * FROM item_swap_logs WHERE status = 1 ORDER BY swapped_at DESC;
SELECT * FROM item_swap_logs WHERE status = 2 ORDER BY received_at DESC;

-- -----------------------------------------------------------------------------
-- J. Gate pass print log
-- -----------------------------------------------------------------------------
SELECT * FROM stock_out_gate_pass_logs ORDER BY created_at DESC;

-- -----------------------------------------------------------------------------
-- K. Requisition / quotation
-- -----------------------------------------------------------------------------
SELECT * FROM requisitions ORDER BY id DESC;
SELECT * FROM quotations ORDER BY id DESC;

-- -----------------------------------------------------------------------------
-- L. Item master search
-- -----------------------------------------------------------------------------
SELECT id, item_title, category_id, code FROM item_info WHERE status <> 9 ORDER BY item_title;

-- -----------------------------------------------------------------------------
-- M. Category IT Equipment (CSV import default)
-- -----------------------------------------------------------------------------
SELECT * FROM category
WHERE LOWER(category_title) LIKE '%it equipment%'
   OR LOWER(category) LIKE '%it equipment%';

-- -----------------------------------------------------------------------------
-- N. Duplicate SET_UNIQ_NO check (data quality)
-- -----------------------------------------------------------------------------
SELECT set_uniq_no, COUNT(*) AS cnt
FROM item_inventory
WHERE status <> 9
  AND LENGTH(TRIM(set_uniq_no)) > 0
GROUP BY set_uniq_no
HAVING COUNT(*) > 1;

-- -----------------------------------------------------------------------------
-- O. Branch inventory count by branch_id
-- -----------------------------------------------------------------------------
SELECT branch_id, COUNT(DISTINCT set_uniq_no) AS set_count
FROM item_inventory
WHERE status <> 9
  AND LENGTH(TRIM(set_uniq_no)) > 0
GROUP BY branch_id
ORDER BY set_count DESC;

-- -----------------------------------------------------------------------------
-- P. Soft-deleted rows
-- -----------------------------------------------------------------------------
SELECT COUNT(*) FROM item_inventory WHERE status = 9;

-- END QUERIES
