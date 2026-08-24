<?php
declare(strict_types=1);

/**
 * functions/dca.php
 * SmartPort AI - Dollar-Cost Averaging (DCA) Simulator & Compounding Engine
 * จำลองการออมหุ้นถัวเฉลี่ยรายเดือน คำนวณดอกเบี้ยทบต้น และเปรียบเทียบผลตอบแทนกับเงินฝากและดัชนีตลาด
 * ปฏิบัติตามมาตรฐาน Strict Types และ Early Return Pattern
 */

/**
 * จำลองการเติบโตของการลงทุนแบบ Dollar-Cost Averaging (DCA)
 *
 * @param float $monthlyAmount เงินลงทุนรายเดือน (บาท)
 * @param int $years ระยะเวลาลงทุน (ปี)
 * @param float $annualGrowthRate อัตราเติบโตของราคาหุ้นต่อปี (CAGR %)
 * @param float $dividendYield อัตราเงินปันผลตอบแทนต่อปี (Yield %)
 * @param bool $reinvestDividends นำเงินปันผลกลับมาลงทุนต่อหรือไม่ (DRIP)
 * @return array
 */
function simulateDcaGrowth(
    float $monthlyAmount,
    int $years,
    float $annualGrowthRate,
    float $dividendYield,
    bool $reinvestDividends = true
): array {
    // Guard Clauses: ปรับแต่งค่าความถูกต้อง
    $monthlyAmount = max(100.0, $monthlyAmount);
    $years = max(1, min(30, $years));
    $annualGrowthRate = max(-50.0, min(100.0, $annualGrowthRate));
    $dividendYield = max(0.0, min(30.0, $dividendYield));

    $totalMonths = $years * 12;

    // อัตราผลตอบแทนรายเดือน
    $monthlyGrowthRate = $annualGrowthRate != 0 ? pow(1 + ($annualGrowthRate / 100), 1 / 12) - 1 : 0.0;
    $monthlyDivRate = ($dividendYield / 100) / 12;

    // Benchmark Rates (เงินฝาก 1.5% APY และ SET Index 7.0% APY)
    $bankMonthlyRate = pow(1 + 0.015, 1 / 12) - 1;
    $indexMonthlyRate = pow(1 + 0.070, 1 / 12) - 1;

    $stockPortfolioValue = 0.0;
    $totalPrincipal = 0.0;
    $totalDividendsPaid = 0.0;
    $bankValue = 0.0;
    $indexValue = 0.0;

    $yearlyBreakdown = [];
    $currentYearDividends = 0.0;

    for ($m = 1; $m <= $totalMonths; $m++) {
        // 1. เพิ่มเงินต้นรายเดือน
        $totalPrincipal += $monthlyAmount;
        $stockPortfolioValue += $monthlyAmount;
        $bankValue += $monthlyAmount;
        $indexValue += $monthlyAmount;

        // 2. เติบโตตามผลตอบแทนราคา
        $stockPortfolioValue *= (1 + $monthlyGrowthRate);
        $bankValue *= (1 + $bankMonthlyRate);
        $indexValue *= (1 + $indexMonthlyRate);

        // 3. จ่ายเงินปันผล
        $monthDividend = $stockPortfolioValue * $monthlyDivRate;
        $totalDividendsPaid += $monthDividend;
        $currentYearDividends += $monthDividend;

        // หากเลือก DRIP (Reinvest) ให้นำปันผลบวกกลับเข้ามูลค่าพอร์ต
        if ($reinvestDividends) {
            $stockPortfolioValue += $monthDividend;
        }

        // 4. บันทึก Amortization รายปี
        if ($m % 12 === 0) {
            $yearNum = (int)($m / 12);
            $capitalGain = $stockPortfolioValue - $totalPrincipal;
            $roiPct = ($totalPrincipal > 0) ? ($capitalGain / $totalPrincipal) * 100 : 0.0;

            $yearlyBreakdown[] = [
                'year'                => $yearNum,
                'principal'           => $totalPrincipal,
                'stock_value'         => $stockPortfolioValue,
                'capital_gain'        => $capitalGain,
                'annual_dividend'     => $currentYearDividends,
                'cumulative_dividend' => $totalDividendsPaid,
                'bank_value'          => $bankValue,
                'index_value'         => $indexValue,
                'roi_pct'             => $roiPct
            ];

            $currentYearDividends = 0.0;
        }
    }

    $finalCapitalGain = $stockPortfolioValue - $totalPrincipal;
    $finalTotalReturnPct = ($totalPrincipal > 0) ? ($finalCapitalGain / $totalPrincipal) * 100 : 0.0;

    return [
        'monthly_amount'       => $monthlyAmount,
        'years'                => $years,
        'annual_growth_rate'   => $annualGrowthRate,
        'dividend_yield'       => $dividendYield,
        'reinvest_dividends'   => $reinvestDividends,
        'total_principal'      => $totalPrincipal,
        'final_stock_value'    => $stockPortfolioValue,
        'final_capital_gain'   => $finalCapitalGain,
        'total_dividends_paid' => $totalDividendsPaid,
        'total_return_pct'     => $finalTotalReturnPct,
        'final_bank_value'     => $bankValue,
        'final_index_value'    => $indexValue,
        'yearly_breakdown'     => $yearlyBreakdown
    ];
}
