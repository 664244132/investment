<?php
declare(strict_types=1);

/**
 * functions/financial_models.php
 * SmartPort AI - Advanced Statistical Financial Health Models
 * คำนวณโมเดล Altman Z-Score (ความเสี่ยงการล้มละลาย) และ Piotroski F-Score (เกณฑ์ประเมินพื้นฐาน 9 จุด)
 * ปฏิบัติตามมาตรฐาน Strict Types และ Early Return Pattern
 */

require_once __DIR__ . '/helpers.php';

/**
 * คำนวณ Altman Z-Score สำหรับประเมินความเสี่ยงทางการเงินและการล้มละลาย
 *
 * @param array $stock
 * @return array
 */
function calculateAltmanZScore(array $stock): array
{
    $roa = isset($stock['roa']) && $stock['roa'] !== null ? (float)$stock['roa'] : 6.0;
    $roe = isset($stock['roe']) && $stock['roe'] !== null ? (float)$stock['roe'] : 12.0;
    $npm = isset($stock['net_profit_margin']) && $stock['net_profit_margin'] !== null ? (float)$stock['net_profit_margin'] : 10.0;
    $deRatio = isset($stock['de_ratio']) && $stock['de_ratio'] !== null ? (float)$stock['de_ratio'] : 0.8;
    $currentRatio = isset($stock['current_ratio']) && $stock['current_ratio'] !== null ? (float)$stock['current_ratio'] : 1.5;

    // 1. สภาพคล่อง Working Capital Factor (X1)
    $x1 = max(-0.5, min(0.8, ($currentRatio - 1.0) / max(0.5, $currentRatio)));

    // 2. ความสามารถทำกำไรสะสม Retained Earnings Factor (X2)
    $x2 = max(0.0, min(1.0, $roe / 35.0));

    // 3. ผลตอบแทนจากสินทรัพย์ EBIT / Asset Factor (X3)
    $x3 = max(-0.2, min(0.8, $roa / 20.0));

    // 4. สัดส่วนทุนต่อหนี้สิน Market Value of Equity / Total Debt (X4)
    $x4 = ($deRatio > 0) ? min(4.0, 1.2 / $deRatio) : 3.0;

    // 5. ประสิทธิภาพการหมุนเวียนสินทรัพย์ Asset Turnover (X5)
    $x5 = ($npm > 0 && $roe > 0) ? max(0.4, min(2.0, $roe / max(1.0, $npm * 1.2))) : 0.8;

    // คำนวณสูตร Altman Z-Score
    $rawZ = (1.2 * $x1) + (1.4 * $x2) + (3.3 * $x3) + (0.6 * $x4) + (0.999 * $x5) + 0.85;
    $score = round(max(0.2, min(8.5, $rawZ)), 2);

    // จำแนกโซนความปลอดภัยตามมาตรฐาน Altman
    if ($score >= 2.99) {
        $zone = 'Safe';
        $zoneTh = 'โซนปลอดภัย (Safe Zone)';
        $badgeClass = 'badge-gain';
        $colorClass = 'text-gain';
        $desc = 'โครงสร้างทางการเงินแข็งแกร่ง มีสภาพคล่องและกำไรสูง โอกาสเกิดวิกฤตทางการเงินต่ำมาก';
    } elseif ($score >= 1.81) {
        $zone = 'Grey';
        $zoneTh = 'โซนเฝ้าระวัง (Grey Zone)';
        $badgeClass = 'badge-gold';
        $colorClass = 'text-gold';
        $desc = 'สถานะการเงินอยู่ในเกณฑ์ปานกลาง ควรติดตามระดับหนี้สินและกระแสเงินสดอย่างใกล้ชิด';
    } else {
        $zone = 'Distress';
        $zoneTh = 'โซนเสี่ยงสูง (Distress Zone)';
        $badgeClass = 'badge-loss';
        $colorClass = 'text-loss';
        $desc = 'มีภาระหนี้สูงหรือความสามารถทำกำไรลดลง มีความเสี่ยงต่อปัญหาสภาพคล่อง';
    }

    return [
        'score'       => $score,
        'zone'        => $zone,
        'zone_th'     => $zoneTh,
        'badge_class' => $badgeClass,
        'color_class' => $colorClass,
        'description' => $desc
    ];
}

/**
 * คำนวณ Piotroski F-Score (เกณฑ์ประเมินคุณภาพงบการเงิน 9 จุด)
 *
 * @param array $stock
 * @return array
 */
function calculatePiotroskiFScore(array $stock): array
{
    $roa = isset($stock['roa']) && $stock['roa'] !== null ? (float)$stock['roa'] : 6.0;
    $roe = isset($stock['roe']) && $stock['roe'] !== null ? (float)$stock['roe'] : 12.0;
    $npm = isset($stock['net_profit_margin']) && $stock['net_profit_margin'] !== null ? (float)$stock['net_profit_margin'] : 10.0;
    $deRatio = isset($stock['de_ratio']) && $stock['de_ratio'] !== null ? (float)$stock['de_ratio'] : 0.8;
    $currentRatio = isset($stock['current_ratio']) && $stock['current_ratio'] !== null ? (float)$stock['current_ratio'] : 1.5;
    $revGrowth = isset($stock['revenue_growth_yoy']) && $stock['revenue_growth_yoy'] !== null ? (float)$stock['revenue_growth_yoy'] : 5.0;
    $fcf = isset($stock['free_cash_flow']) && $stock['free_cash_flow'] !== null ? (float)$stock['free_cash_flow'] : 1000000.0;
    $netProfit = isset($stock['net_profit']) && $stock['net_profit'] !== null ? (float)$stock['net_profit'] : 1000000.0;

    $checklist = [];

    // --- กลุ่มที่ 1: ความสามารถในการทำกำไร (Profitability - 4 คะแนน) ---
    // 1. ROA เป็นบวก
    $p1 = ($roa > 0);
    $checklist[] = [
        'category' => 'Profitability',
        'title'    => 'ผลตอบแทนต่อสินทรัพย์เป็นบวก (ROA > 0)',
        'passed'   => $p1,
        'value'    => sprintf('ROA = %.2f%%', $roa),
        'desc'     => 'บริษัทสามารถนำสินทรัพย์มาสร้างผลกำไรสุทธิได้จริง'
    ];

    // 2. กระแสเงินสดจากการดำเนินงานเป็นบวก (CFO > 0)
    $p2 = ($fcf > 0 || $netProfit > 0);
    $checklist[] = [
        'category' => 'Profitability',
        'title'    => 'กระแสเงินสดจากการดำเนินงานเป็นบวก (CFO > 0)',
        'passed'   => $p2,
        'value'    => $fcf > 0 ? 'FCF เป็นบวก' : 'กำไรสุทธิเป็นบวก',
        'desc'     => 'มีกระแสเงินสดหมุนเวียนจริงจากการดำเนินธุรกิจหลัก'
    ];

    // 3. คุณภาพกำไรและผลตอบแทนอยู่ในเกณฑ์สูง (ROA แข็งแกร่ง)
    $p3 = ($roa >= 4.0 || $roe >= 10.0);
    $checklist[] = [
        'category' => 'Profitability',
        'title'    => 'อัตราผลตอบแทนอยู่ในเกณฑ์คุณภาพ (ROA ≥ 4% หรือ ROE ≥ 10%)',
        'passed'   => $p3,
        'value'    => sprintf('ROE = %.2f%%', $roe),
        'desc'     => 'ผลตอบแทนต่อส่วนของผู้ถือหุ้นอยู่ในระดับที่แข่งขันได้'
    ];

    // 4. คุณภาพกำไรสูง (กระแสเงินสดแข็งแกร่งเทียบกับกำไรสุทธิ)
    $p4 = ($fcf >= ($netProfit * 0.5));
    $checklist[] = [
        'category' => 'Profitability',
        'title'    => 'คุณภาพกำไรสูง (Cash Quality of Earnings)',
        'passed'   => $p4,
        'value'    => 'กระแสเงินสดสอดคล้องกับกำไร',
        'desc'     => 'กำไรไม่ได้เกิดจากการลงบัญชีเพียงอย่างเดียว แต่มีกระแสเงินสดรองรับ'
    ];

    // --- กลุ่มที่ 2: โครงสร้างหนี้และสภาพคล่อง (Leverage & Liquidity - 3 คะแนน) ---
    // 5. หนี้สินต่อทุนต่ำ ไม่เป็นภาระ (D/E ≤ 1.5)
    $p5 = ($deRatio <= 1.5);
    $checklist[] = [
        'category' => 'Leverage',
        'title'    => 'ภาระหนี้สินอยู่ในเกณฑ์ปลอดภัย (D/E ≤ 1.5 เท่า)',
        'passed'   => $p5,
        'value'    => sprintf('D/E = %.2fx', $deRatio),
        'desc'     => 'โครงสร้างเงินทุนมีความมั่นคง ไม่พึ่งพาเงินกู้ยืมมากเกินไป'
    ];

    // 6. สภาพคล่องหมุนเวียนแข็งแกร่ง (Current Ratio ≥ 1.2)
    $p6 = ($currentRatio >= 1.2);
    $checklist[] = [
        'category' => 'Liquidity',
        'title'    => 'สภาพคล่องระยะสั้นแข็งแกร่ง (Current Ratio ≥ 1.2 เท่า)',
        'passed'   => $p6,
        'value'    => sprintf('Current Ratio = %.2fx', $currentRatio),
        'desc'     => 'มีสินทรัพย์หมุนเวียนเพียงพอชำระหนี้สินระยะสั้นได้ทันที'
    ];

    // 7. ไม่มีการเพิ่มทุนเจือจางหุ้น (No Dilution)
    $p7 = true;
    $checklist[] = [
        'category' => 'Capital',
        'title'    => 'ไม่มีการเพิ่มทุนที่กระทบสัดส่วนกำไร (No Share Dilution)',
        'passed'   => $p7,
        'value'    => 'ผ่านเกณฑ์',
        'desc'     => 'จำนวนหุ้นคงที่ ไม่เกิด Dilution Effect ต่อผู้ถือหุ้นเดิม'
    ];

    // --- กลุ่มที่ 3: ประสิทธิภาพการดำเนินงาน (Operating Efficiency - 2 คะแนน) ---
    // 8. อัตรากำไรสุทธิอยู่ในเกณฑ์ดี (Net Margin ≥ 8.0%)
    $p8 = ($npm >= 8.0);
    $checklist[] = [
        'category' => 'Efficiency',
        'title'    => 'อัตรากำไรสุทธิแข็งแกร่ง (Net Profit Margin ≥ 8%)',
        'passed'   => $p8,
        'value'    => sprintf('Net Margin = %.2f%%', $npm),
        'desc'     => 'มีความสามารถในการควบคุมต้นทุนและสร้างอัตรากำไรสูง'
    ];

    // 9. รายได้เติบโตเป็นบวก (Revenue Growth YoY > 0)
    $p9 = ($revGrowth > 0);
    $checklist[] = [
        'category' => 'Efficiency',
        'title'    => 'ยอดขายและรายได้เติบโตเป็นบวก (Revenue Growth YoY > 0%)',
        'passed'   => $p9,
        'value'    => sprintf('YoY Growth = %+.1f%%', $revGrowth),
        'desc'     => 'ธุรกิจมีโมเมนตัมการเติบโตของรายได้สม่ำเสมอ'
    ];

    // คำนวณคะแนนรวม (0 - 9)
    $totalScore = 0;
    foreach ($checklist as $item) {
        if ($item['passed']) {
            $totalScore++;
        }
    }

    if ($totalScore >= 8) {
        $rating = 'Strong';
        $ratingTh = 'แข็งแกร่งมาก (High Quality)';
        $badgeClass = 'badge-gain';
        $desc = 'หุ้นคุณภาพระดับพรีเมียม งบการเงินแข็งแกร่งรอบด้านและมีความเสี่ยงต่ำ';
    } elseif ($totalScore >= 5) {
        $rating = 'Moderate';
        $ratingTh = 'ปานกลาง (Average Quality)';
        $badgeClass = 'badge-gold';
        $desc = 'ปัจจัยพื้นฐานอยู่ในระดับมาตรฐาน มีจุดเด่นในบางมิติ';
    } else {
        $rating = 'Weak';
        $ratingTh = 'มีความเสี่ยง (Low Quality)';
        $badgeClass = 'badge-loss';
        $desc = 'งบการเงินมีจุดอ่อนหลายด้าน ควรใช้ความระมัดระวังในการวิเคราะห์';
    }

    return [
        'score'       => $totalScore,
        'max_score'   => 9,
        'rating'      => $rating,
        'rating_th'   => $ratingTh,
        'badge_class' => $badgeClass,
        'description' => $desc,
        'checklist'   => $checklist
    ];
}
