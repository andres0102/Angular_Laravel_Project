<?php
$show_ytd = $data['show_agent_ytd'];
$personnel = $data['personnel'];
$summary = $data['payroll']['summary'];
$summary_aa = $summary['era']['aa'];
$summary_lfa = $summary['era']['lfa'];
$summary_gi = $summary['era']['gi'];
$summary_commission = $summary['commission'];
$computations = $data['payroll']['computations'];
$basic_cat_array = ['health', 'life', 'trailer', 'cis', 'ebh', 'gi'];
$abc = [null,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
$roman = [null,'i','ii','iii','iv','v','vi','vii','viii','ix','x','xi','xii','xiii','xiv','xv','xvi','xvii','xviii','xix','xx','xxi','xxii','xxiii','xxiv','xxv','xxvi','xxvii','xxviii','xxix','xxx']; ?>
@component('mail::message')
<!-- # Congratulations -->
<span class="email_greetings">Dear {{ $personnel['name'] }},</span><br>
We are pleased to enclose the payroll summary of your commissions for {{ $data['payroll_month'] . ' ' . $data['payroll_year'] }}.

For your convenience, we have attached a printable PDF version of this output together with this email.

<!-- Begin Payroll Computations :: Summary Table -->

@component('mail::panel')
<h2>Payroll Summary for {{ $data['payroll_month'] . ' ' . $data['payroll_year'] }}</h2>
<div class="export-table summary">
  <table class="table" width="100%" border="0" cellpadding="5">
    <thead>
        <tr>
            <th align="left" width="140">Agency</th>
            <th align="left">Payroll Type</th>
            <th align="right" width="160">Commission Type</th>
            <th align="center" width="130">{{ $data['payroll_month'] . ' ' . $data['payroll_year'] }}</th>
            <?php if ($show_ytd) { ?><th align="center" width="130">Year-to-Date</th><?php } ?>
        </tr>
    </thead>
    <tbody>
        <tr height="1">
          <td align="left" rowspan="6"><b>LEGACY FA</b></td>
          <td align="left" rowspan="6"><b>BASIC COMMISSIONS</b>
            <div>
              <table class="condensed">
                <tr><td width="120">LFA BANDING</td><td>: Rank {{ $personnel['banding_lfa']['rank'] ?? 'N/A' }} ({{ sprintf("%.2f%%", ((float)$personnel['rate_lfa'] ?? 0) * 100) }})</td></tr>
                <tr><td width="120">GI BANDING</td><td>: {{ ($personnel['banding_gi']['rank']) ? 'Rank ' . $personnel['banding_gi']['rank'] . ' (' . sprintf("%.2f%%", ((float)$personnel['rate_gi'] ?? 0) * 100) . ')' : 'N/A' }}</td></tr>
                <tr><td width="120">MTD CLIENT COUNT</td><td>: {{ $summary['basic_client_count'] ?? 0 }}</td></tr>
                <tr><td width="120">MTD POLICY COUNT</td><td>: {{ $summary['basic_policy_count'] ?? 0 }}</td></tr>
              </table>
            </div>
          </td>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">FIRST YEAR</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['mtd']['basic']['first-year'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['ytd']['basic']['first-year'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">RENEWAL</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['mtd']['basic']['renewal'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['ytd']['basic']['renewal'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">TRAILER</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['mtd']['basic']['trailer'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['ytd']['basic']['trailer'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">ADVISORY FEE</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['mtd']['basic']['advisory-fee'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_lfa['ytd']['basic']['advisory-fee'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">GENERAL INSURANCE</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_gi['mtd']['basic']['total'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_gi['ytd']['basic']['total'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr>
          <td align="center" class="subtotal"></td>
          <td align="center" class="subtotal"></td>
          <td align="right" class="subtotal">SUB-TOTAL</td>
          <td align="center" class="subtotal">$ <money>{{ number_format(($summary_lfa['mtd']['basic']['total'] + $summary_gi['mtd']['basic']['total']), 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal">$ <money>{{ number_format(($summary_lfa['ytd']['basic']['total'] + $summary_gi['ytd']['basic']['total']), 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <?php if($personnel['has_override']) { // Start Override Test ?>
    <tbody>
        <tr height="1">
          <td align="left" rowspan="5"><b>LEGACY FA</b></td>
          <td align="left" rowspan="5"><b>OVERRIDING COMMISSIONS</b>
            <div>
              <table class="condensed">
                <tr><td colspan="2"><?= $personnel['designation'] ?></td></tr>
                <tr><td width="120">SALES TIER</td><td>: <?= $personnel['salesforce_tier'] ?></td></tr>
                <tr><td width="120">{{ ($personnel['salesforce_tier'] == 3) ? 'GROUP' : 'UNIT' }} (ACTIVE)</td><td>: {{ count($summary['agents']) }} PERSONNEL</td></tr>
                <tr><td width="120">OR HEADCOUNT</td><td>: {{ count($summary['agents']) + count($summary['resigned_agents']) + count($summary['inherit_agents']) }} PERSONNEL</td></tr>
              </table>
            </div>
          </td>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">UNIT</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['mtd']['or']['unit'] + $summary_gi['mtd']['or']['unit']), 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['ytd']['or']['unit'] + $summary_gi['ytd']['or']['unit']), 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">GROUP</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['mtd']['or']['group'] + $summary_gi['mtd']['or']['group']), 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['ytd']['or']['group'] + $summary_gi['ytd']['or']['group']), 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">INHERIT</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['mtd']['or']['inherit'] + $summary_gi['mtd']['or']['inherit']), 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format(($summary_lfa['ytd']['or']['inherit'] + $summary_gi['ytd']['or']['inherit']), 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="client"><td colspan="3"></td></tr>
        <tr>
          <td align="center" class="subtotal"></td>
          <td align="center" class="subtotal"></td>
          <td align="right" class="subtotal">SUB-TOTAL</td>
          <td align="center" class="subtotal">$ <money>{{ number_format(($summary_lfa['mtd']['or']['total'] + $summary_gi['mtd']['or']['total']), 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal">$ <money>{{ number_format(($summary_lfa['ytd']['or']['total'] + $summary_gi['ytd']['or']['total']), 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <?php } // End Override Test ?>
    <?php if($personnel['has_aa']) { // Start AA Test ?>
    <tbody>
        <tr height="1">
          <td align="left" rowspan="5"><b>AVIVA ADVISERS</b></td>
          <td align="left" rowspan="5"><b>BASIC COMMISSIONS</b>
            <div>
              <table class="condensed">
                <tr><td width="120">MTD CLIENT COUNT</td><td>: <?= $summary['basic_client_count_aa'] ?? 0 ?></td></tr>
                <tr><td width="120">MTD POLICY COUNT</td><td>: <?= $summary['basic_policy_count_aa'] ?? 0 ?></td></tr>
              </table>
            </div>
          </td>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">FIRST YEAR</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['basic']['first-year'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['basic']['first-year'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">RENEWAL</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['basic']['renewal'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['basic']['renewal'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">TRAILER</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['basic']['trailer'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['basic']['trailer'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">BUPA</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['basic']['bupa'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['basic']['bupa'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr>
          <td align="center" class="subtotal"></td>
          <td align="center" class="subtotal"></td>
          <td align="right" class="subtotal">SUB-TOTAL</td>
          <td align="center" class="subtotal">$ <money>{{ number_format($summary_aa['mtd']['basic']['total'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal">$ <money>{{ number_format($summary_aa['ytd']['basic']['total'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <?php if($personnel['has_override'] && $personnel['has_aa_override']) { // Start Override Test ?>
    <tbody>
        <tr height="1">
          <td align="left" rowspan="4"><b>AVIVA ADVISERS</b></td>
          <td align="left" rowspan="4"><b>OVERRIDING COMMISSIONS</b>
            <div>
              <table class="condensed">
                <tr><td colspan="2"><?= $personnel['designation_aa'] ?></td></tr>
                <tr><td width="120">SALES TIER</td><td>: <?= $personnel['salesforce_tier_aa'] ?></td></tr>
              </table>
            </div>
          </td>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">UNIT</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['or']['unit'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['or']['unit'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">GROUP</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['or']['group'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['or']['group'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr class="condensed-row">
          <td class="condensed-cell" align="right">INHERIT</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['mtd']['or']['inherit'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_aa['ytd']['or']['inherit'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr>
          <td align="center" class="subtotal"></td>
          <td align="center" class="subtotal"></td>
          <td align="right" class="subtotal">SUB-TOTAL</td>
          <td align="center" class="subtotal">$ <money>{{ number_format($summary_aa['mtd']['or']['total'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal">$ <money>{{ number_format($summary_aa['ytd']['or']['total'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <?php } // End Override Test ?>
    <?php } // End AA Test ?>
    <tbody style="background: white;">
        <tr>
          <td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important;"></td>
          <td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important;"></td>
          <td align="right" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important;">GROSS COMMISSIONS</td>
          <td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important;">$ <money>{{ number_format($summary_commission['total']['mtd']['gross-commission'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important;">$ <money>{{ number_format($summary_commission['total']['ytd']['gross-commission'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <tbody style="background: white;">
        <tr><td colspan="5" class="subtotal" style="padding: 0;"></td></tr>
        <tr>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="right">BASIC ADJUSTMENTS</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['basic']['mtd']['nett-adjustments'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['basic']['ytd']['nett-adjustments'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="right">OR ADJUSTMENTS</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['or']['mtd']['nett-adjustments'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['or']['ytd']['nett-adjustments'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
        <tr>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="center"></td>
          <td class="condensed-cell" align="right">ELITE SCHEME</td>
          <td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['elite']['mtd'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td class="condensed-cell" align="center">$ <money>{{ number_format($summary_commission['elite']['ytd'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
    <tbody>
        <tr style="background: white; border-bottom: 4px double #ddd;">
          <td align="center" class="subtotal summary"></td>
          <td align="center" class="subtotal summary"></td>
          <td align="right" class="subtotal summary">NETT COMMISSIONS</td>
          <td align="center" class="subtotal summary">$ <money>{{ number_format($summary_commission['total']['mtd']['nett-commission'], 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal summary">$ <money>{{ number_format($summary_commission['total']['ytd']['nett-commission'], 2,'.', ',') }}</money></td><?php } ?>
        </tr>
    </tbody>
  </table>
</div>
@endcomponent

<!-- Begin Payroll Computations :: Breakdown Table -- Adjustments -->

<?php
foreach([['type' => 'basic', 'name' => 'Basic Adjustments'], ['type' => 'or', 'name' => 'Overriding Adjustments'], ['type' => 'elite', 'name' => 'Elite Scheme Payments']] as $adj_type) {
  if ($computations['adjustments'] && count($computations['adjustments'][$adj_type['type']]) > 0) {
    $adj_count = 0;
?>
@component('mail::panel')
<h3>{{ $adj_type['name'] }}</h3>
<div class="export-table">
  <table class="table no-footer-border" width="100%" border="0" cellpadding="5">
    <thead>
        <tr>
            <th align="left" width="50">S/N</th>
            <th align="left" colspan="2">Adjustment Description</th>
            <th align="center" width="130">Amount</th>
        </tr>
    </thead>
    <?php foreach($computations['adjustments'][$adj_type['type']] as $adjustment_comp) { $adj_count++; ?>
    <tbody>
        <tr class="client">
            <td align="left"><b><?= $adj_count ?>.</b></td>
            <td align="left" colspan="2"><b><?= strtoupper($adjustment_comp->record->transaction_desc) ?></b></td>
            <td align="center"><b>$ <money><?= number_format($adjustment_comp['amount'], 2,'.', ',') ?></money></b></td>
        <tr>
    </tbody>
    <?php } // end foreach ?>
    <?php if ($adj_type['type'] != 'elite') { // if not elite ?>
    <tbody style="background: white;">
        <tr>
          <td align="left" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important; border-bottom: 1px solid #EDEFF2;"></td>
          <td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important; border-bottom: 1px solid #EDEFF2;"></td>
          <td align="right" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important; border-bottom: 1px solid #EDEFF2;">SUB-TOTAL</td>
          <td align="center" class="subtotal summary condensed-cell" style="padding-top: 10px !important; padding-bottom: 8px !important; border-bottom: 1px solid #EDEFF2;">$ <money>{{ number_format($summary_commission[$adj_type['type']]['mtd']['gross-adjustments'], 2,'.', ',') }}</money></td>
        </tr>
    </tbody>
    <tbody style="background: white;">
        <tr><td colspan="4" class="subtotal" style="padding: 0;"></td></tr>
        <tr>
          <td class="condensed-cell" align="left" style="border-bottom: 1px solid #EDEFF2;"></td>
          <td class="condensed-cell" align="center" style="border-bottom: 1px solid #EDEFF2;"></td>
          <td class="condensed-cell" align="right" style="border-bottom: 1px solid #EDEFF2;">BALANCE C/F</td>
          <td class="condensed-cell" align="center" style="border-bottom: 1px solid #EDEFF2;">$ <money>{{ number_format($summary_commission[$adj_type['type']]['mtd']['carry-forward-adjustments'], 2,'.', ',') }}</money></td>
        </tr>
    </tbody>
    <?php } // end if not elite ?>
    <tbody>
      <tr style="background: white; border-bottom: 4px double #ddd;">
          <td align="left" class="subtotal summary" colspan="2" style="padding-left: 12px;">MONTH-TO-DATE (MTD) RECORDS: <?= $adj_count ?></td>
          <td align="right" class="subtotal summary" width="300">MTD {{ strtoupper($adj_type['name']) }}</td>
          <td align="center" class="subtotal summary">$ <money>{{ number_format((($adj_type['type'] == 'elite') ? $summary_commission[$adj_type['type']]['mtd'] : $summary_commission[$adj_type['type']]['mtd']['nett-adjustments']), 2,'.', ',') }}</money></td>
      </tr>
    </tbody>
  </table>
</div>
@endcomponent
  <?php
  }
} ?>

<hr>
The breakdown of the payroll computations for your basic commissions in {{ $data['payroll_month'] . ' ' . $data['payroll_year'] }} is available for your reference below.

<!-- Begin Payroll Computations :: Breakdown Table -- Basic Commissions -->
<?php
$basic_insurance_type_count = 0;
foreach($basic_cat_array as $insurance_type) {
  $basic_insurance_type_count++;
  $cat_client_count = $cat_policy_count = $cat_total_premium = $cat_total_gross_revenue = $cat_total_commission = 0; ?>
@component('mail::panel')
<h1>Section <?= $abc[$basic_insurance_type_count] ?> - <?= $computations['basic'][$insurance_type]['title'] ?> (Basic Commissions)</h1>
<div class="export-table">
  <table class="table no-footer-border" width="100%" border="0" cellpadding="5">
    <thead>
        <tr>
            <th align="left" width="50">S/N</th>
            <th align="left" colspan="2">Client List</th>
            <th align="center" width="35"></th>
            <th align="center" width="130">Premium</th>
            <th align="center" width="130">Gross Revenue</th>
            <th align="center" width="130">Nett Commission</th>
        </tr>
    </thead>
    <?php if ($comp_breakdown = $computations['basic'][$insurance_type]['breakdown']) { $client_count = 0; $policy_count = 0; ?>
    <?php foreach($comp_breakdown as $comp_by_client) { $client_count++; $cat_client_count++; $policy_count = 0;
              $total_premium_of_client = $total_rev_of_client = $total_comm_of_client = 0;
              foreach($comp_by_client as $c_b_c) {
                foreach ($c_b_c as $c_b_p) {
                  $total_premium_of_client += $c_b_p->record->premium;
                  $total_rev_of_client += $c_b_p->record->commission;
                }
                $total_comm_of_client += $c_b_c->sum('amount');
              }
    ?>
    <tbody>
        <tr class="client">
            <td align="left"><b><?= $client_count ?>.</b></td>
            <td align="left" colspan="3"><b><?= $comp_by_client->first()->first()->client->name ?></b></td>
            <td align="center"><b>$ <money><?= number_format($total_premium_of_client, 2,'.', ',') ?></money></b></td>
            <td align="center"><b>$ <money><?= number_format($total_rev_of_client, 2,'.', ',') ?></money></b></td>
            <td align="center"><b>$ <money><?= number_format($total_comm_of_client, 2,'.', ',') ?></money></b></td>
        <tr>
        <?php foreach($comp_by_client as $comp_by_policy) { $policy_count++; $cat_policy_count++; ?>
            <tr class="client" height="1">
                <td rowspan="<?= ($comp_by_policy->count() + 2) ?>" class="fillers"></td>
                <td align="left" width="35" rowspan="<?= ($comp_by_policy->count() + 2) ?>" style="text-transform: lowercase;"><?= $abc[$policy_count] ?>.</td>
                <td algn="left" rowspan="<?= ($comp_by_policy->count() + 2) ?>">
                  <?php
                  $provider_alias = strtoupper($comp_by_policy->first()->provider_alias) . " ";
                  $policy_name = strtoupper($comp_by_policy->first()->transaction->name);
                  $pre_title = (str_contains($policy_name, $provider_alias)) ? "" : $provider_alias;
                  ?>
                  <div style="font-size: 11px; padding-bottom: 0px;"><?= $pre_title ?><?= $policy_name ?></div>
                  <div style="font-size: 8px; line-height: 5px; padding-bottom: 10px; font-style: italic;"><?= $comp_by_policy->first()->provider_name ?></div>
                  <div>
                    <table class="condensed">
                      <tr><td width="110">Policy No</td><td>: <?= $comp_by_policy->first()->policy->policy_no ?></td></tr>
                      <tr><td width="110">Comm Type</td><td>: <?= $comp_by_policy->pluck('commission_type')->unique()->implode(', ') ?></td></tr>
                      <tr><td width="110">Incepted On</td><td>: <?= $comp_by_policy->first()->policy->date_inception ?></td></tr>
                      <tr><td width="110">Payment Freq</td><td>: <?= $comp_by_policy->first()->policy->payment_frequency ?></td></tr>
                      <tr><td width="110">Transactions</td><td>: <?= $comp_by_policy->count() ?></td></tr>
                    </table>
                  </div>
                </td>
            </tr>
            <?php
              $trxn_count = $total_premium = $total_gross_revenue = $total_commission = 0;
              if ($comp_by_policy->count() >= 1) {
              foreach($comp_by_policy as $policy_comp_breakdown) {
                  $trxn_count++;
                  $cat_total_premium += (float)$policy_comp_breakdown->record->premium;
                  $cat_total_gross_revenue += (float)$policy_comp_breakdown->record->commission;
                  $cat_total_commission += (float)$policy_comp_breakdown->amount; ?>
                  <tr class="condensed-row">
                      <td align="left" class="condensed-cell" height="1" style="text-transform: lowercase;"><i><?= $roman[$trxn_count] ?? $trxn_count ?>.</i></money></td>
                      <td align="center" class="condensed-cell" height="1">$ <money><?= number_format($policy_comp_breakdown->record->premium, 2,'.', ',') ?></money></td>
                      <td align="center" class="condensed-cell" height="1">$ <money><?= number_format($policy_comp_breakdown->record->commission, 2,'.', ',') ?></money></td>
                      <td align="center" class="condensed-cell" height="1">$ <money><?= number_format($policy_comp_breakdown->amount, 2,'.', ',') ?></money></td>
                  </tr>
                  <?php if ($trxn_count == $comp_by_policy->count()) { ?>
                    <tr class="client"><td align="center" colspan="4"></td></tr>
                  <?php } // End if ?>
            <?php } // End foreach ?>
            <?php } // End foreach ?>
            <?php } // End if ?>

        </tbody>
        <?php } // End foreach ?>
    <?php } else { // if-else ?>
    <tbody>
      <tr><td colspan="8" ></td></tr>
      <tr><td colspan="8" align="center">No commission records found.</td></tr>
      <tr><td colspan="8" ></td></tr>
    </tbody>
    <?php } // end if ?>
    <tbody>
      <tr style="background: white; border-bottom: 4px double #ddd;">
          <td align="left" class="subtotal summary" colspan="4" style="padding-left: 12px;">TOTAL CLIENTS: <?= $cat_client_count ?> <small><i>(<?= $cat_policy_count ?: "NO" ?> POLICIES)</i></small></td>
          <td align="center" class="subtotal summary">$ <money>{{ number_format($cat_total_premium, 2,'.', ',') }}</money></td>
          <td align="center" class="subtotal summary">$ <money>{{ number_format($cat_total_gross_revenue, 2,'.', ',') }}</money></td>
          <td align="center" class="subtotal summary">$ <money>{{ number_format($cat_total_commission, 2,'.', ',') }}</money></td>
      </tr>
    </tbody>
  </table>
</div>
@endcomponent
<hr>
<?php } // End foreach ?>

<!-- End Payroll Computations :: Breakdown Table -- Basic Commissions -->

<!-- Begin Payroll Computations :: Breakdown Table -- OR Commissions -->
<?php if($personnel['has_override']) { // Start Override Test?>
The breakdown of the payroll computations for your overriding commissions in {{ $data['payroll_month'] . ' ' . $data['payroll_year'] }} is available for your reference below.
<?php foreach([['title' => 'Active', 'comp' => 'agents'],['title' => 'Inactive', 'comp' => 'resigned_agents'],['title' => 'Inherit', 'comp' => 'inherit_agents']] as $or_group) {
  $or_agent_count = $or_total_agent_mtd = $or_total_agent_ytd = $or_mtd_sum = $or_ytd_sum = 0; ?>
@component('mail::panel')
<?php if ($personnel['salesforce_tier'] == 3) { ?><h1>Director Group - <?= $or_group['title'] ?> Personnel List (Overriding Commissions)</h1><?php } ?>
<?php if ($personnel['salesforce_tier'] == 2) { ?><h1>Managerial Unit - <?= $or_group['title'] ?> Personnel List (Overriding Commissions)</h1><?php } ?>
<div class="export-table">
  <table class="table" width="100%" border="0" cellpadding="5">
    <thead>
        <tr>
            <th align="left" width="50">S/N</th>
            <th align="left">Personnel</th>
            <?php if ($or_group['title'] == 'Active') { ?><th align="center" width="80"></th><?php } ?>
            <?php if ($or_group['title'] == 'Active') { ?><th align="center" width="130">Agent MTD</th><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><th align="center" width="130">Agent YTD</th><?php } ?>
            <?php if ($or_group['title'] != 'Active') { ?><th align="center" width="100" style="color:red;">Date Resigned</th><?php } ?>
            <?php if ($or_group['title'] != 'Active') { ?><th align="center" width="100" style="color:red;">Last Day</th><?php } ?>
            <th align="center" width="130">Override MTD</th>
            <?php if ($show_ytd) { ?><th align="center" width="130">Override YTD</th><?php } ?>
        </tr>
    </thead>
    <?php if ($summary[$or_group['comp']] && count($summary[$or_group['comp']]) > 0) { ?>
    <?php foreach($summary[$or_group['comp']] as $or_agent) {
      $or_agent_count++;
      $or_total_agent_mtd += ($or_agent['mtd']['total'] ?? 0);
      $or_total_agent_ytd += ($or_agent['ytd']['total'] ?? 0);
      $or_mtd_sum += $or_agent['or_mtd']['total'];
      $or_ytd_sum += $or_agent['or_ytd']['total'];
      ?>
    <tbody>
        <tr class="client">
            <td align="left"><b><?= $or_agent_count ?>.</b></td>
            <td colspan="<?= ($or_group['title'] == 'Active') ? '2' : '1' ?>"><b><?= $or_agent['name'] ?> <span style="color:red;font-style:italic;">{{($or_group['title'] == 'Active' && ($or_agent['date_resigned'] || $or_agent['last_day'])) ? '(Resigned)' : ''}}</span></b></div></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1"><b>$ <money>{{ number_format($or_agent['mtd']['total'], 2,'.', ',') }}</money></b></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1"><b>$ <money>{{ number_format($or_agent['ytd']['total'], 2,'.', ',') }}</money></b></td><?php } ?>
            <?php if ($or_group['title'] != 'Active') { ?><td align="center" height="1" style="color:red;"><?= $or_agent['date_resigned'] ?? "-" ?></td><?php } ?>
            <?php if ($or_group['title'] != 'Active') { ?><td align="center" height="1" style="color:red;"><?= $or_agent['last_day'] ?? "-" ?></td><?php } ?>
            <td align="center" height="1"><b>$ <money>{{ ($or_agent['or_mtd']['total'] != 0) ? number_format($or_agent['or_mtd']['total'], 2,'.', ',') : '-' }}</money></b></td>
            <?php if ($show_ytd) { ?><td align="center" height="1"><b>$ <money>{{ ($or_agent['or_ytd']['total'] != 0) ? number_format($or_agent['or_ytd']['total'], 2,'.', ',') : '-' }}</money></b></td><?php } ?>
        </tr>
      <?php if ($or_group['title'] == 'Active') { ?>
        <tr class="client" height="1">
            <?php
              $agentinfo_row_count = 2;
              if ($or_agent['has_lfa_basic']) $agentinfo_row_count++;
              if ($or_agent['has_lfa_or']) $agentinfo_row_count++;
              if ($or_agent['has_aa_basic']) $agentinfo_row_count++;
              if ($or_agent['has_aa_or']) $agentinfo_row_count++;
              if ($or_agent['has_adjustments']) $agentinfo_row_count++;
              if ($or_agent['has_elite']) $agentinfo_row_count++;
            ?>
            <td rowspan="{{ $agentinfo_row_count }}"></td>
            <td rowspan="{{ $agentinfo_row_count }}">
              <div>
                <table class="condensed">
                  <tr><td colspan="2"><?= $or_agent['designation'] ?></td></tr>
                  <tr><td width="90">Since</td><td>: <?= $or_agent['first_day'] ?> (<?= $or_agent['length'] ?>)</td></tr>
                  <tr><td width="90">LFA Code</td><td>: <?= $or_agent['lfa_code'] ?></td></tr>
                  <?php if ($or_agent['supervisor']) { ?><tr><td width="90">Supervisor</td><td>: <?= $or_agent['supervisor'] ?></td></tr><?php } ?>
                  <?php if ($or_agent['date_resigned'] || $or_agent['last_day']) { ?><tr><td width="90" style="color:red;">Resigned</td><td style="color:red;">: <?= $or_agent['date_resigned'] ?? "-" ?></td></tr><?php } ?>
                  <?php if ($or_agent['date_resigned'] || $or_agent['last_day']) { ?><tr><td width="90" style="color:red;">Last Day</td><td style="color:red;">: <?= $or_agent['last_day'] ?? "-" ?></td></tr><?php } ?>
                </table>
              </div>
            </td>
        </tr>
        <?php if ($or_agent['has_lfa_basic']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>LFA BASIC</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['lfa-basic'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['lfa-basic'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money><?= ($or_agent['or_mtd']['lfa'] != 0) ? number_format($or_agent['or_mtd']['lfa'], 2,'.', ',') : '-' ?></money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= ($or_agent['or_ytd']['lfa'] != 0) ? number_format($or_agent['or_ytd']['lfa'], 2,'.', ',') : '-' ?></money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <?php if ($or_agent['has_lfa_or']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>LFA OR</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['lfa-or'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['lfa-or'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money>-</money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money>-</money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <?php if ($or_agent['has_aa_basic']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>AA BASIC</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['aa-basic'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['aa-basic'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money><?= ($or_agent['or_mtd']['aa'] != 0) ? number_format($or_agent['or_mtd']['aa'], 2,'.', ',') : '-' ?></money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= ($or_agent['or_ytd']['aa'] != 0) ? number_format($or_agent['or_ytd']['aa'], 2,'.', ',') : '-' ?></money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <?php if ($or_agent['has_aa_or']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>AA OR</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['aa-or'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['aa-or'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money>-</money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money>-</money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <?php if ($or_agent['has_adjustments']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>ADJ.</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['adjustments'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['adjustments'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money>-</money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money>-</money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <?php if ($or_agent['has_elite']) { ?>
        <tr class="condensed-row">
            <td align="left" height="1" class="condensed-cell"><i>ELITE</i></td>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['mtd']['elite'], 2,'.', ',') ?></money></td><?php } ?>
            <?php if ($or_group['title'] == 'Active') { ?><td align="center" height="1" class="condensed-cell">$ <money><?= number_format($or_agent['ytd']['elite'], 2,'.', ',') ?></money></td><?php } ?>
            <td align="center" height="1" class="condensed-cell">$ <money>-</money></td>
            <?php if ($show_ytd) { ?><td align="center" height="1" class="condensed-cell">$ <money>-</money></td><?php } ?>
        </tr>
        <?php } // End if ?>
        <tr class="client"><td colspan="{{ ($or_group['title'] == 'Active') ? '7' : '5' }}"></td></tr>
      <?php } // End if ?>
    </tbody>
    <?php } // End foreach ?>
    <?php } else { // else-if ?>
    <tbody>
      <tr><td colspan="7"></td></tr>
      <tr><td colspan="7" align="center">No personnel records found.</td></tr>
      <tr><td colspan="7"></td></tr>
    </tbody>
    <?php }  // End if ?>
    <tbody>
      <tr style="background: white; border-bottom: 4px double #ddd;">
          <td align="left" class="subtotal summary" colspan="{{ ($or_group['title'] == 'Active') ? '3' : '4' }}" style="padding-left: 12px;">TOTAL {{$or_group['title']}} PERSONNEL: <?= $or_agent_count ?></td>
          <?php if ($or_group['title'] == 'Active') { ?><td align="center" class="subtotal summary">$ <money>{{ number_format($or_total_agent_mtd, 2,'.', ',') }}</money></td><?php } ?>
          <?php if ($or_group['title'] == 'Active' && $show_ytd) { ?><td align="center" class="subtotal summary">$ <money>{{ number_format($or_total_agent_ytd, 2,'.', ',') }}</money></td><?php } ?>
          <td align="center" class="subtotal summary">$ <money>{{ number_format($or_mtd_sum, 2,'.', ',') }}</money></td>
          <?php if ($show_ytd) { ?><td align="center" class="subtotal summary">$ <money>{{ number_format($or_ytd_sum, 2,'.', ',') }}</money></td><?php } ?>
      </tr>
    </tbody>
  </table>
</div>
@endcomponent
<hr>
<?php } // End foreach ?>
<?php } // End Override Test ?>


<!-- For your convenience, we have also attached a printable PDF version of this output together with this email. -->
Please do not hesitate to inform us if you found any discrepancies in the payroll computation.



<span class="email_regards">Warmest Regards,</span><br>
<span class="email_name">Finance Department</span><br>
<span class="email_job">Legacy FA Pte Ltd</span>
@endcomponent
