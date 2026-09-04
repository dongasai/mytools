-- ====================================================================
-- 数据修正脚本：referral_relations.referred_user_id 字段错误修正
--
-- Bug描述：referred_user_id 字段错误存储 account_id 而非 user_id
-- 修复目标：批量修正所有记录，将 account_id 转换为对应的 user_id
--
-- 执行时机：代码修复完成后，在测试环境验证后再在生产环境执行
-- 影响范围：referral_relations 表所有记录
-- 风险等级：高（核心业务数据）
-- ====================================================================

-- Step 1: 数据备份（创建临时备份表）
DROP TABLE IF EXISTS referral_relations_backup_20260522;

CREATE TABLE referral_relations_backup_20260522 AS
SELECT * FROM referral_relations;

-- Step 2: 数据验证（检查备份完整性）
SELECT
    'backup_count' AS check_type,
    COUNT(*) AS backup_count,
    NOW() AS backup_time
FROM referral_relations_backup_20260522;

-- Step 3: 验证当前错误数据（查看需要修正的记录）
SELECT
    rr.id AS relation_id,
    rr.referral_code,
    rr.referrer_user_id,
    rr.referred_user_id AS wrong_account_id,
    u.id AS correct_user_id,
    u.account_id,
    u.username,
    CASE
        WHEN rr.referred_user_id = u.account_id THEN '需要修正'
        WHEN rr.referred_user_id = u.id THEN '数据正确'
        ELSE '异常数据'
    END AS status
FROM referral_relations rr
LEFT JOIN user_users u ON u.account_id = rr.referred_user_id
ORDER BY rr.id;

-- Step 4: 执行批量修正（将 account_id 替换为 user_id）
UPDATE referral_relations rr
INNER JOIN user_users u ON u.account_id = rr.referred_user_id
SET rr.referred_user_id = u.id
WHERE rr.referred_user_id != u.id;  -- 只修正错误的记录

-- Step 5: 验证修正结果
SELECT
    '修正完成' AS status,
    COUNT(*) AS total_relations,
    COUNT(CASE WHEN referred_user_id > 10000 THEN 1 END) AS user_id_range_records,
    COUNT(CASE WHEN referred_user_id < 100 THEN 1 END) AS account_id_range_records,
    NOW() AS fix_time
FROM referral_relations;

-- Step 6: 详细验证修正后的数据
SELECT
    rr.id,
    rr.referral_code,
    rr.referrer_user_id,
    rr.referred_user_id AS fixed_user_id,
    u.id AS verify_user_id,
    u.account_id AS verify_account_id,
    u.username,
    CASE
        WHEN rr.referred_user_id = u.id THEN '✅ 正确'
        ELSE '❌ 错误'
    END AS validation_result
FROM referral_relations rr
INNER JOIN user_users u ON u.id = rr.referred_user_id
ORDER BY rr.id DESC
LIMIT 20;

-- Step 7: 验证推荐关系查询功能（使用修正后的数据）
-- 测试：查询用户10083的推荐关系
SELECT
    rr.id,
    rr.referral_code,
    rr.referrer_user_id,
    rr.referred_user_id,
    referrer_user.username AS referrer_username,
    referred_user.username AS referred_username
FROM referral_relations rr
LEFT JOIN user_users referrer_user ON referrer_user.id = rr.referrer_user_id
LEFT JOIN user_users referred_user ON referred_user.id = rr.referred_user_id
WHERE rr.referred_user_id = 10083
   OR rr.referrer_user_id = 10083;

-- Step 8: 统计推荐关系数据完整性
SELECT
    '数据完整性统计' AS report_type,
    COUNT(*) AS total_relations,
    COUNT(DISTINCT referrer_user_id) AS unique_referrers,
    COUNT(DISTINCT referred_user_id) AS unique_referred_users,
    COUNT(CASE WHEN u1.id IS NOT NULL THEN 1 END) AS valid_referrer_count,
    COUNT(CASE WHEN u2.id IS NOT NULL THEN 1 END) AS valid_referred_count,
    COUNT(CASE WHEN u1.id IS NULL THEN 1 END) AS invalid_referrer_count,
    COUNT(CASE WHEN u2.id IS NULL THEN 1 END) AS invalid_referred_count,
    NOW() AS report_time
FROM referral_relations rr
LEFT JOIN user_users u1 ON u1.id = rr.referrer_user_id
LEFT JOIN user_users u2 ON u2.id = rr.referred_user_id;

-- ====================================================================
-- 回滚脚本（如果修正失败，可从备份表恢复）
-- ====================================================================

-- 回滚方案1：从备份表恢复（谨慎操作）
-- DROP TABLE IF EXISTS referral_relations;
-- CREATE TABLE referral_relations AS SELECT * FROM referral_relations_backup_20260522;

-- 回滚方案2：重新修正（反向修正）
-- UPDATE referral_relations rr
-- INNER JOIN user_users u ON u.id = rr.referred_user_id
-- SET rr.referred_user_id = u.account_id;

-- ====================================================================
-- 清理备份表（确认修正成功后执行）
-- ====================================================================

-- DROP TABLE IF EXISTS referral_relations_backup_20260522;

-- ====================================================================
-- 执行建议
-- ====================================================================
-- 1. 在测试环境先执行并验证
-- 2. 生产环境执行前做好数据库完整备份
-- 3. 分步执行，每步验证后再执行下一步
-- 4. 保留备份表至少一周，确认无问题后再清理
-- 5. 执行时间选择业务低峰期
-- ====================================================================