/**
 * @license
 * Copyright TWISS.IO
 * All Rights Reserved.
 * Licensed under the MIT License.
 */
import { Component, HostListener, Inject, OnDestroy, ViewChild, ElementRef } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { AuthService, ApiService } from '@app/@core/services';
import { Subscription } from 'rxjs';

import { environment } from '@env/environment';

@Component({
  selector: 'associate-dashboard',
  templateUrl: './associate-dashboard.component.html',
  styleUrls: ['./associate-dashboard.component.scss']
})
export class AssociateDashboardComponent implements OnDestroy {
  associate_change_subscription: Subscription;

  selected_range = null;
  // selected_year = (new Date()).getFullYear();
  selected_year = null;
  selected_month = null;
  monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
  name = `${environment.appName}`;

  summary_chart_title = null;
  summary_chart_latest_month_title = null;
  summary_chart_data;
  summary_chart_labels = [];
  summary_chart_null = [];
  summary_chart_initialized = false;
  summary_chart_loaded = false;
  summary_chart_hidden = false;
  summary_chart_options = {
    maintainAspectRatio: false,
    plugins: {
      deferred: {
        xOffset: '100%', // defer until 150px of the canvas width are inside the viewport
        yOffset: '100%', // defer until 50% of the canvas height are inside the viewport
        delay: 12500 // delay of 500 ms after the canvas is considered inside the viewport
      }
    },
    legend: {
      display: true,
      labels: {
        fontFamily: 'Montserrat',
        fontSize: 10,
        fontStyle: '500',
        fontColor: '#aaa',
        padding: 15,
        usePointStyle: true,
        filter: function(item, chart) { return (item.text) ? (!item.text.includes('Nett Commissions') && !item.text.includes('Gross Revenue')) : false; },
      },
      onClick: function (e, legendItem) {
        var index = legendItem.datasetIndex;
        var ci = this.chart;
        var meta = ci.getDatasetMeta(index);
        var hiddenItems = 0;
        meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
        ci.update();
        this.chart.legend.legendItems.forEach(function(item) { if (item.hidden) hiddenItems++; });
        var datasets = this.chart.data.datasets;
        datasets[datasets.length - 1].hidden = (hiddenItems != 0);
        ci.update();
      },
    },
    scales: {
      yAxes: [{
        stacked: true,
        gridLines: {
          zeroLineColor: 'rgba(255, 255, 255, 0.15)',
          color: 'rgba(255, 255, 255, 0.05)',
        },
        ticks: {
          fontColor: '#bbb',
          beginAtZero: true,
          callback: (value, index, values) => {
            return '$' + this.kFormatter(value);
          }
        }
      }],
      xAxes: [{
        stacked: true,
        // barThickness: 20,
        gridLines: {
          display: false,
        },
        ticks: {
          fontColor: '#bbb',
          callback: (value, index, values) => {
            return (this.selected_range == 'year') ? value.substring(0, 3) : value;
          }
        }
      }],

    },
    tooltips: {
      enabled: false,
      custom: function(tooltipModel) {
        // Tooltip Element
        var ngxChart = <HTMLElement>document.getElementsByClassName('chart-container')[0];
        var columnEl = document.getElementById('chartjs-column-highlight');

        // Create element on first render
        if (!columnEl) {
          columnEl = document.createElement('div');
          columnEl.id = 'chartjs-column-highlight';
          document.body.appendChild(columnEl);
        }

        // Tooltip Element
        var tooltipEl = document.getElementById('chartjs-tooltip');

        // Create element on first render
        if (!tooltipEl) {
          tooltipEl = document.createElement('div');
          tooltipEl.id = 'chartjs-tooltip';
          tooltipEl.innerHTML = '<table></table>';
          document.body.appendChild(tooltipEl);
        }

        // Hide if no tooltip
        if (tooltipModel.opacity === 0) {
          tooltipEl.style.opacity = '0';
          columnEl.style.opacity = '0';
          return;
        } else {
          tooltipEl.style.opacity = tooltipModel.opacity;
        }

        function getBody(bodyItem) {
          return bodyItem.lines;
        }

        // Set Text
        if (tooltipModel.body) {
            let titleLines = tooltipModel.title || [];
            let bodyLines = tooltipModel.body.map(getBody);
            let linesCount = 0;
            let emptyLine = '<tr><td>No Data Found</td><td>-</td><tr>';

            let innerHtml = '<thead>';

            titleLines.forEach(function(title) {
                innerHtml += '<tr><th colspan="2">' + title + '</th></tr>';
            });
            innerHtml += '</thead><tbody>';

            bodyLines.forEach(function(body, i) {
              if (body.length !== 0) {
                linesCount++;
                if (body[0].includes('Nett Commissions') || body[0].includes('Gross Revenue')) {
                  if (linesCount === 1) innerHtml += emptyLine;
                  innerHtml += '<tr class="total"><td>' + body + '</td></tr>';
                } else {
                  let style = '<span class="data-color" style="background: ' + tooltipModel.labelColors[i].backgroundColor + '"></span>';
                  innerHtml += '<tr><td>' + style + body + '</td></tr>';
                }
              }
            });

            if (linesCount == 0) innerHtml += emptyLine;
            innerHtml += '</tbody>';

            let tableRoot = tooltipEl.querySelector('table');
            tableRoot.innerHTML = innerHtml;
        }

        // `this` will be the overall tooltip
        let position = this._chart.canvas.getBoundingClientRect();
        let xAxis = this._chart.scales['x-axis-0'];
        let yAxis = this._chart.scales['y-axis-0'];
        let columnWidth = xAxis.width / xAxis._valueRange;
        let caretWidth = 9;

        // Configure styles for tooltip
        tooltipEl.classList.add('d-none');
        tooltipEl.classList.add('d-sm-block');
        tooltipEl.style.opacity = '1';
        let posX = (position.left + tooltipModel.caretX);
        if ((posX + tooltipEl.offsetWidth + (columnWidth/2) + caretWidth) > position.right) {
          tooltipEl.classList.remove('caret-left');
          tooltipEl.classList.add('caret-right');
          tooltipEl.style.left = posX - tooltipEl.offsetWidth - (columnWidth/2) - caretWidth + 'px';
        } else {
          tooltipEl.classList.remove('caret-right');
          tooltipEl.classList.add('caret-left');
          tooltipEl.style.left = posX + (columnWidth/2) + caretWidth + 'px';
        }
        let posY = (position.top + tooltipModel.caretY);
        tooltipEl.style.top = ((posY + tooltipEl.offsetHeight) > position.bottom) ? (position.bottom - tooltipEl.offsetHeight + 'px') : (posY + 'px');

        // Configure styles for column highlight
        columnEl.style.opacity = '1';
        columnEl.style.width = columnWidth + 'px';
        columnEl.style.height = yAxis.height + 'px';
        columnEl.style.top = ngxChart.offsetTop + yAxis.top + 'px';
        columnEl.style.left = posX + 'px';
      },
      mode: 'index',
      intersect: false,
      callbacks: {
        title: (tooltipItem) => {
            return (this.summary_chart_data) ? this.summary_chart_data['labels'][tooltipItem[0].index] + ' - Summary' : '';
        },
        label: (tooltipItem, data) => {
          let xLabel = data.datasets[tooltipItem.datasetIndex].label;

          if ((xLabel == "Nett Commissions" || xLabel == "Gross Revenue") && tooltipItem.yLabel < 0) {
            return [
                xLabel + '</td><td>$0.00</td></tr>' +
                '<tr class="balance-cf"><td>Balance C/F</td><td class="font-source-code">' + Math.abs(tooltipItem.yLabel).toLocaleString("en-US",{style:"currency", currency:"USD"})
              ];
          } else if ((xLabel == "Nett Commissions" || xLabel == "Gross Revenue") && tooltipItem.yLabel >= 0) {
            return xLabel + '</td><td class="font-source-code">' + tooltipItem.yLabel.toLocaleString("en-US",{style:"currency", currency:"USD"});
          } else {
            return (tooltipItem.yLabel == 0) ? null : (xLabel + '</td><td class="font-source-code">' + tooltipItem.yLabel.toLocaleString("en-US",{style:"currency", currency:"USD"}));
          }
        }
      }
    }
  };

  // provider_chart_data;
  // provider_chart_visible = true;
  // provider_chart_loaded = false;
  // provider_chart_legend = {};
  // provider_chart_options = {
  //   maintainAspectRatio: false,
  //   plugins: {
  //     deferred: {
  //       xOffset: '100%', // defer until 150px of the canvas width are inside the viewport
  //       yOffset: '100%', // defer until 50% of the canvas height are inside the viewport
  //       delay: 12500 // delay of 500 ms after the canvas is considered inside the viewport
  //     }
  //   }
  // };

  leaderboard = [];
  leaderboard_loaded = false;
  leaderboard_title = null;
  leaderboard_desc = null;

  summary_data = [];

  getBody(bodyItem) {
      return bodyItem.lines;
  }

  kFormatter(num) {
    return Math.abs(num) > 999 ? (Math.sign(num) * (Math.abs(num)/1000)).toString() + 'k' : (Math.sign(num) * Math.abs(num))
  }

  moveTooltip(evt) {
    let tooltip = document.getElementById('chartjs-tooltip');
    if (tooltip) tooltip.style.top = evt.pageY - (tooltip.offsetHeight/2) + "px";
  }

  formatSummaryData(datasets) {
    return {
      labels: this.summary_chart_labels,
      datasets: datasets,
    };
  }

  getSummary(switchval = null) {
    let datasets = [];

    this.summary_chart_hidden = false;
    this.leaderboard_title = 'FYC Reps Ranking';
    // this.summary_chart_data = this.formatSummaryData([]);

    let year = parseInt(this.selected_year);
    let month = parseInt(this.selected_month);

    if (switchval) {
      switch (this.selected_range) {
        case 'month':
          if (switchval == "previous") {
            if (month == 1) {
              month = 12;
              year--;
            } else {
              month--;
            }
          } else if (switchval == "next") {
            if (month == 12) {
              month = 1;
              year++;
            } else {
              month++;
            }
          }

          break;
        case 'year':
          if (switchval == "previous") {
            year--;
          } else if (switchval == "next") {
            year++;
          }

          break;
      }
    }

    this.apiService.post('dashboard/summary/associates', {
      'year': year,
      'month': month,
      'range': this.selected_range,
      'associate_uuid': (this.authService.associate) ? this.authService.associate.uuid : null
    }).subscribe((result) => {
      // console.log(this.selected_range, result);
      if (result['summary'] == []) {
        this.summary_chart_hidden = true;
      } else {
        this.summary_data['year'] = result['data']['year'];
        this.summary_data['month'] = result['data']['month'];
        this.summary_data['submissions_count'] = result['data']['submissions_count'];
        this.summary_data['policies_count'] = result['data']['policies_count'];
        this.summary_data['clients_count'] = result['data']['clients_count'];
        this.summary_data['earliest'] = result['data']['earliest'] || false;
        this.summary_data['latest'] = result['data']['latest'] || false;
        this.selected_year = result['data']['year'];
        this.selected_month = result['data']['month'];

        this.getLeaderboard();

        let datatypes = [
          { slug: 'elite', label: 'Elite Scheme', color: '#ffffff' },
          { slug: 'first-year', label: 'First Year', color: 'rgba(255,255,255,0.75)' },
          { slug: 'renewal', label: 'Renewal', color: 'rgba(255,255,255,0.5)' },
          { slug: 'trailer', label: 'Trailer', color: 'rgba(255,255,255,0.25)' },
          { slug: 'advisory-fee', label: 'Advisory Fee', color: 'rgba(255,255,255,0.075)' },
          { slug: 'general-insurance', label: 'General Insurance', color: '#85b785' },
          { slug: 'incentives', label: 'Incentives', color: '#ffd47b' },
          { slug: 'others', label: 'Adjustments', color: 'rgba(255,100,100,0.4)' },
          { slug: 'overridding', label: 'Management Fee', color: 'rgba(148, 205, 255, 0.6)' },
        ];

        // Process summary charts for associate
        switch (this.selected_range) {
          case 'month':
            let arr_nett = [];
            let arr_values = [];
            arr_values['first-year'] = [];
            arr_values['renewal'] = [];
            arr_values['trailer'] = [];
            arr_values['advisory-fee'] = [];
            arr_values['general-insurance'] = [];
            arr_values['elite'] = [];
            arr_values['incentives'] = [];
            arr_values['others'] = [];
            arr_values['overridding'] = [];

            this.summary_chart_title = result['data']['month_name'] + ' ' + result['data']['year'] + ' Payroll Computations';
            this.summary_chart_latest_month_title = result['data']['month_name'] + ' ' + result['data']['year'] + ((this.summary_data['latest'])?' (Latest)':((this.summary_data['earliest'])?' (Earliest)':''));
            this.summary_chart_labels.forEach(sc_type => {
              let tslug = null;
              let tcolor = null;
              let value = 0;

              for(let _data of datatypes) {
                if (_data['label'] == sc_type) {
                  tslug = _data['slug'];
                  tcolor = _data['color'];
                  value = result['summary'][tslug];
                  arr_values[tslug].push(value);
                } else {
                  arr_values[_data['slug']].push(0);
                }
              }

              arr_nett.push(value);
            });

            datatypes.forEach(type => {
              let dtslug = type['slug'];
              let dtlabel = type['label'];
              let dtcolor = type['color'];

              datasets.push({
                label: dtlabel,
                data: arr_values[dtslug],
                backgroundColor: dtcolor,
              });
            });

            datasets.push({
              label: 'Nett Commissions',
              data: arr_nett,
              type: 'line'
            });

            break;

          case 'year':
            this.summary_chart_title = this.selected_year + ' Year-to-Date Payroll Computations';

            datatypes.forEach(type => {
              let tslug = type['slug'];
              let tlabel = type['label'];
              let tcolor = type['color'];

              if (JSON.stringify(result['summary'][tslug]) !== JSON.stringify(this.summary_chart_null)) {
                datasets.push({
                  label: tlabel,
                  data: result['summary'][tslug],
                  backgroundColor: tcolor,
                });
              }
            });

            datasets.push({
              label: 'Nett Commissions',
              data: result['summary']['total'],
              type: 'line'
            });
            break;

          // case 'all-time':
          //   datatypes.forEach(type => {
          //     let tslug = type['slug'];
          //     let tlabel = type['label'];
          //     let tcolor = type['color'];

          //     if (JSON.stringify(result['summary'][tslug]) !== JSON.stringify(this.summary_chart_null)) {
          //       datasets.push({
          //         label: tlabel,
          //         data: result['summary'][tslug],
          //         backgroundColor: tcolor,
          //       });
          //     }
          //   });

          //   datasets.push({
          //     label: 'Nett Commissions',
          //     data: result['summary']['total'],
          //     type: 'line'
          //   });
          //   break;
        }


          /**
          if (this.selected_range == 'year') {
            // Process summary charts for selected fa representative (individual)
            if (JSON.stringify(result['summary']['elite']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Elite Scheme',
                data: result['summary']['elite'],
                backgroundColor: '#ffffff',
              });
            }
            if (JSON.stringify(result['summary']['first-year']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'First Year',
                data: result['summary']['first-year'],
                backgroundColor: 'rgba(255,255,255,0.75)',
              });
            }
            if (JSON.stringify(result['summary']['renewal']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Renewal',
                data: result['summary']['renewal'],
                backgroundColor: 'rgba(255,255,255,0.5)',
              });
            }
            if (JSON.stringify(result['summary']['trailer']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Trailer',
                data: result['summary']['trailer'],
                backgroundColor: 'rgba(255,255,255,0.25)',
              });
            }
            if (JSON.stringify(result['summary']['advisory-fee']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Advisory Fee',
                data: result['summary']['advisory-fee'],
                backgroundColor: 'rgba(255,255,255,0.075)',
              });
            }
            if (JSON.stringify(result['summary']['general-insurance']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'General Insurance',
                data: result['summary']['general-insurance'],
                backgroundColor: 'rgb(60, 95, 148)',
              });
            }
            if (JSON.stringify(result['summary']['others']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Others',
                data: result['summary']['others'],
                backgroundColor: 'rgba(255,100,100,0.2)',
              });
            }
            datasets.push({
              label: 'Nett Commissions',
              data: result['summary']['total'],
              type: 'line'
            });
          } else if (this.selected_range == 'team' && result['summary'].length) {
            // Process summary charts for selected fa representative (team)
            if (JSON.stringify(result['summary']['legacy-fa']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Legacy FA',
                data: result['summary']['legacy-fa'],
                backgroundColor: 'rgba(255,255,255,0.75)',
              });
            }
            if (JSON.stringify(result['summary']['aviva-advisers']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Aviva Advisers',
                data: result['summary']['aviva-advisers'],
                backgroundColor: 'rgba(255,255,255,0.5)',
              });
            }
            if (JSON.stringify(result['summary']['general-insurance']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'General Insurance',
                data: result['summary']['general-insurance'],
                backgroundColor: 'rgb(60, 95, 148)',
              });
            }
            if (JSON.stringify(result['summary']['others']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Others',
                data: result['summary']['others'],
                backgroundColor: 'rgba(255,100,100,0.2)',
              });
            }
            if (JSON.stringify(result['summary']['inherit']) !== JSON.stringify(this.summary_chart_null)) {
              datasetsCount++;
              datasets.push({
                label: 'Inherited',
                data: result['summary']['inherit'],
                backgroundColor: 'rgba(255,255,255,0.075)',
              });
            }
            datasets.push({
              label: 'Nett Commissions',
              data: result['summary']['total'],
              type: 'line'
            });
          } else if (this.selected_range == 'organisation' && result['summary'].length) {
            // Process summary charts for organisation (admin-only)
            datasetsCount++;
            datasets.push({
              label: 'Firm Revenue (15%)',
              data: result['summary']['firm_revenue'],
              backgroundColor: '#ffffff',
            });
            datasets.push({
              label: 'Orphan Revenue',
              data: result['summary']['orphan_revenue'],
              backgroundColor: 'rgba(255,255,255,0.75)',
            });
            datasets.push({
              label: 'Basic Commissions',
              data: result['summary']['basic_commissions'],
              backgroundColor: 'rgba(255,255,255,0.5)',
            });
            datasets.push({
              label: 'OR Commissions',
              data: result['summary']['or_commissions'],
              backgroundColor: 'rgba(255,255,255,0.25)',
            });
            datasets.push({
              label: 'Gross Revenue',
              data: result['summary']['gross_revenue'],
              type: 'line'
            });
          } else if (!this.selected_range && result['summary'].length) {
            // Process basic summary charts for firm-wide (staff-access)
            datasetsCount++;
            datasets.push({
              label: '# of Clients (Legacy FA)',
              data: result['summary']['lfa_clients_count'],
              backgroundColor: 'rgba(255,255,255,0.75)',
              stack: 1
            });
            datasets.push({
              label: '# of Clients (AVIVA Advisers)',
              data: result['summary']['aa_clients_count'],
              backgroundColor: 'rgba(255,255,255,0.25)',
              stack: 1
            });
            datasets.push({
              label: '# of Policies (Legacy FA)',
              data: result['summary']['lfa_policies_count'],
              backgroundColor: 'rgba(100,100,255,0.75)',
              stack: 2
            });
            datasets.push({
              label: '# of Policies (AVIVA Advisers)',
              data: result['summary']['aa_policies_count'],
              backgroundColor: 'rgba(100,100,255,0.25)',
              stack: 2
            });
          }
          **/

          this.summary_chart_loaded = true;
          this.summary_chart_data = this.formatSummaryData(datasets);

          // console.log('Total Datasets', datasets.length);
          this.summary_chart_options = this.summary_chart_options;

          if (!this.summary_chart_initialized) {
            setTimeout(() => {
              this.summary_chart_initialized = true;
            }, 2000);
          }
      }
    });
  }

  // updateProviderChartLegend() {
  //   this.provider_chart_legend = {
  //     fullWidth: false,
  //     position: (document.body.offsetWidth >= 992) ? 'right' : 'bottom',
  //     labels: {
  //       fontFamily: 'Montserrat',
  //       fontSize: 10,
  //       fontStyle: '500',
  //       fontColor: '#888',
  //       padding: 15,
  //       usePointStyle: true,
  //     },
  //   };
  // }

  // formatProviderData(labels, datasets) {
  //   return {
  //     labels: labels,
  //     datasets: datasets,
  //   };
  // }

  // getProviderBreakdown(inputRole = null) {
  //   var dynamicColors = function() {
  //     var r = Math.floor(Math.random() * 255);
  //     var g = Math.floor(Math.random() * 255);
  //     var b = Math.floor(Math.random() * 255);
  //     return "rgb(" + r + "," + g + "," + b + ")";
  //   };
  //   this.provider_chart_visible = true;
  //   this.provider_chart_loaded = false;
  //   this.provider_chart_data = [];
  //   this.updateProviderChartLegend();
  //   this.apiService.post('dashboard/providers', {'year': this.selected_year, 'summary_type': inputRole, 'personnel_uuid': (this.authService.associate) ? this.authService.associate.uuid : null}).subscribe((result) => {
  //     // console.log(inputRole, result);
  //     if (!result.error && result.provider_data) {
  //       let datasets = [];
  //       let data = [];
  //       let labels = [];
  //       let colors = [];
  //       result.provider_data.forEach(item => {
  //         labels.push(item.full_name);
  //         data.push(item.count);
  //         colors.push((item.color || dynamicColors()) + 'bb');
  //       });
  //       datasets.push({
  //         label: 'Providers',
  //         data: data,
  //         backgroundColor: colors,
  //       });
  //       this.provider_chart_data = this.formatProviderData(labels, datasets);
  //     } else {
  //       this.provider_chart_visible = false;
  //     }

  //     this.provider_chart_loaded = true;
  //   });
  // }

  init() {
    this.changeRange(this.route.snapshot.paramMap.get('summary_range') || null);
  }

  changeRange(range = null) {
    this.summary_chart_hidden = false;
    this.summary_chart_data = this.formatSummaryData([]);
    this.summary_chart_latest_month_title = null;

    switch (range) {
      case 'month':
        this.selected_range = 'month';
        this.summary_chart_title = 'Latest Payroll Computations';
        this.summary_chart_labels = ['First Year', 'Renewal', 'Trailer', 'Advisory Fee', 'General Insurance', 'Elite Scheme', 'Incentives', 'Adjustments', 'Management Fee'];
        this.summary_chart_null = ["0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00"];
        this.leaderboard_title = 'FYC Reps Ranking (Month)';
        this.router.navigate(['/authenticated/associates/dashboard/month'], {replaceUrl: true});
        break;

      case 'year':
        this.selected_range = 'year';
        this.summary_chart_labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        this.summary_chart_null = ["0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00", "0.00"];
        this.leaderboard_title = 'FYC Reps Ranking (Year)';
        this.router.navigate(['/authenticated/associates/dashboard/year'], {replaceUrl: true});
        break;

      // case 'all-time':
      //   this.selected_range = 'all-time';
      //   this.summary_chart_title = 'All Payroll Computations';
      //   this.summary_chart_labels = [];
      //   this.summary_chart_null = [];
      //   this.leaderboard_title = 'All-Stars Ranking';
      //   this.router.navigate(['/authenticated/associates/dashboard/all-time'], {replaceUrl: true});
      //   for (var _i = 2016; _i <= this.selected_year; _i++) {
      //     this.summary_chart_labels.push(String(_i));
      //     this.summary_chart_null.push("0.00");
      //   }
      //   break;

      default:
        this.changeRange('month');
    }

    this.getSummary();
  }

  getLeaderboard(type = 'associates') {
    this.leaderboard = [];
    this.leaderboard_loaded = false;
    this.apiService.post('dashboard/leaderboard/' + type, {
        'year': this.selected_year,
        'month': this.selected_month,
        'range': this.selected_range,
        'limit': 20
      }).subscribe((result) => {
      if (!result.error && result.data.leaderboard) {
        switch (this.selected_range) {
          case 'month':
            this.leaderboard_title = 'FYC Reps Ranking (' + result['data']['month'] + ' ' + result['data']['year'] + ')'
            // this.leaderboard_desc = '(' + result['data']['month'] + ' ' + result['data']['year'] + ')';
            break;
          case 'year':
            this.leaderboard_title = 'FYC Reps Ranking (' + result['data']['year'] + ')'
            // this.leaderboard_desc = '(' + result['data']['year'] + ')';
            break;
          default:
            this.leaderboard_desc = null;
        }
        this.leaderboard = result.data.leaderboard;
        this.leaderboard_loaded = true;
      }
    });
  }

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private apiService: ApiService,
    private authService: AuthService) {

      this.init();

      this.associate_change_subscription = this.authService.associate_change.subscribe(value => {
        this.init();
      });
  }

  ngOnDestroy () {
    this.associate_change_subscription.unsubscribe();
    // Remove Tooltip
    let tooltip = document.getElementById('chartjs-tooltip');
    if (tooltip) tooltip.remove();
    let tooltip_highlight = document.getElementById('chartjs-column-highlight')
    if (tooltip_highlight) tooltip_highlight.remove();
  }
}
