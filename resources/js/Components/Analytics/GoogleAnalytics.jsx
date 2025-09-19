import { useState, useEffect, useMemo } from 'react';
import AnalyticsFilter from '@/Components/Analytics/AnalyticsFilter';
import KPIMetric from '@/Components/Analytics/KPIMetric';
import DailyPerformanceChart from '@/Components/Analytics/DailyPerformanceChart';
import TrafficAcquisitionChart from '@/Components/Analytics/TrafficAcquisitionChart';
import TopTrafficAcquisitionTable from '@/Components/Analytics/TopTrafficAcquisitionTable';
import TopLandingPagesTable from '@/Components/Analytics/TopLandingPagesTable';
import { format, startOfMonth, endOfMonth, subMonths } from 'date-fns';

const GoogleAnalytics = ({ setting }) => {
  const enableAIAssistant = setting?.enable_ai_assistant ?? false;
  const [filters, setFilters] = useState({
    dateRange: 'last_month',
    days: 30,
    from: startOfMonth(subMonths(new Date(), 1)),
    to: endOfMonth(subMonths(new Date(), 1)),
  });

  const [dailyChartData, setDailyChartData] = useState(null);
  const [kpiData, setKpiData] = useState(null);
  const [trafficChartData, setTrafficChartData] = useState(null);
  const [trafficTableData, setTrafficTableData] = useState([]);
  const [landingPagesData, setLandingPagesData] = useState([]);

  const [dailyChartLoading, setDailyChartLoading] = useState(false);
  const [kpiLoading, setKpiLoading] = useState(false);
  const [trafficChartLoading, setTrafficChartLoading] = useState(false);
  const [trafficTableLoading, setTrafficTableLoading] = useState(false);
  const [landingPagesLoading, setLandingPagesLoading] = useState(false);

  const [trafficPage, setTrafficPage] = useState(1);
  const [trafficPagination, setTrafficPagination] = useState({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [trafficTotals, setTrafficTotals] = useState({
    sessions: 0,
    newUsers: 0,
    totalUsers: 0,
    bounceRate: 0,
  });

  const [landingPage, setLandingPage] = useState(1);
  const [landingPagination, setLandingPagination] = useState({
    current_page: 1,
    per_page: 10,
    total: 0,
    last_page: 1,
  });
  const [landingTotals, setLandingTotals] = useState({
    sessions: 0,
    new_users: 0,
    total_users: 0,
    bounce_rate: 0,
    percentage: 0,
  });

  const handleAIFeedback = (feedbackType, insights) => {
    console.log('AI Feedback received:', feedbackType, insights);
  };

  const buildApiUrl = (endpoint, params = {}) => {
    const url = new URL(`/analytics/api/${endpoint}`, window.location.origin);
    Object.keys(params).forEach(key => {
      if (params[key] !== null && params[key] !== undefined) {
        url.searchParams.append(key, params[key]);
      }
    });
    return url.toString();
  };

  const buildApiParams = (additionalParams = {}) => {
    const baseParams = {
      days: filters.days || 30,
      ...additionalParams,
    };

    if (filters.dateRange && filters.dateRange !== 'custom') {
      baseParams.preset = filters.dateRange;
    } else if (filters.from && filters.to) {
      baseParams.start_date = format(filters.from, 'yyyy-MM-dd');
      baseParams.end_date = format(filters.to, 'yyyy-MM-dd');
      baseParams.preset = 'custom';
    }

    return baseParams;
  };

  const fetchDailyChart = async () => {
    setDailyChartLoading(true);
    try {
      const params = buildApiParams();
      const url = buildApiUrl('daily-chart', params);
      const response = await fetch(url);
      const result = await response.json();

      if (result.success) {
        setDailyChartData(result.data);
      } else {
        setDailyChartData(null);
      }
    } catch (error) {
      setDailyChartData(null);
    } finally {
      setDailyChartLoading(false);
    }
  };

  const fetchKpiMetrics = async () => {
    setKpiLoading(true);
    try {
      const params = buildApiParams();
      const url = buildApiUrl('kpi-metrics', params);
      const response = await fetch(url);
      const result = await response.json();

      if (result.success) {
        setKpiData(result.data);
      } else {
        setKpiData(null);
      }
    } catch (error) {
      setKpiData(null);
    } finally {
      setKpiLoading(false);
    }
  };

  const fetchTrafficChart = async () => {
    setTrafficChartLoading(true);
    try {
      const params = buildApiParams();
      const url = buildApiUrl('traffic-chart', params);
      const response = await fetch(url);
      const result = await response.json();

      if (result.success) {
        setTrafficChartData(result.data);
      } else {
        setTrafficChartData(null);
      }
    } catch (error) {
      setTrafficChartData(null);
    } finally {
      setTrafficChartLoading(false);
    }
  };

  const fetchTrafficTable = async (page = 1) => {
    setTrafficTableLoading(true);
    try {
      const params = buildApiParams({ limit: 10, page });
      const url = buildApiUrl('traffic-table', params);
      const response = await fetch(url);
      const result = await response.json();

      if (result.success && result.data) {
        setTrafficTableData(Array.isArray(result.data.traffic_sources) ? result.data.traffic_sources : []);
        setTrafficPagination(result.data.pagination || {
          current_page: 1,
          per_page: 10,
          total: 0,
          last_page: 1,
        });
        setTrafficTotals(result.data.totals || {
          sessions: 0,
          newUsers: 0,
          totalUsers: 0,
          bounceRate: 0,
        });
        setTrafficPage(page);
      } else {
        setTrafficTableData([]);
        setTrafficPagination({
          current_page: 1,
          per_page: 10,
          total: 0,
          last_page: 1,
        });
        setTrafficTotals({
          sessions: 0,
          newUsers: 0,
          totalUsers: 0,
          bounceRate: 0,
        });
      }
    } catch (error) {
      setTrafficTableData([]);
    } finally {
      setTrafficTableLoading(false);
    }
  };

  const fetchLandingPages = async (page = 1) => {
    setLandingPagesLoading(true);
    try {
      const params = buildApiParams({ limit: 10, page });
      const url = buildApiUrl('landing-pages', params);
      const response = await fetch(url);
      const result = await response.json();

      if (result.success && result.data) {
        setLandingPagesData(Array.isArray(result.data.landing_pages) ? result.data.landing_pages : []);
        setLandingPagination(result.data.pagination || {
          current_page: 1,
          per_page: 10,
          total: 0,
          last_page: 1,
        });
        setLandingTotals(result.data.totals || {
          sessions: 0,
          new_users: 0,
          total_users: 0,
          bounce_rate: 0,
          percentage: 0,
        });
        setLandingPage(page);
      } else {
        setLandingPagesData([]);
        setLandingPagination({
          current_page: 1,
          per_page: 10,
          total: 0,
          last_page: 1,
        });
        setLandingTotals({
          sessions: 0,
          new_users: 0,
          total_users: 0,
          bounce_rate: 0,
          percentage: 0,
        });
      }
    } catch (error) {
      setLandingPagesData([]);
    } finally {
      setLandingPagesLoading(false);
    }
  };

  const handleFilterChange = async newFilters => {
    setFilters(newFilters);
    setTrafficPage(1);
    setLandingPage(1);

    // Use newFilters directly instead of the state
    const buildApiParamsWithFilters = (additionalParams = {}) => {
      const baseParams = {
        days: newFilters.days || 30,
        ...additionalParams,
      };

      if (newFilters.dateRange && newFilters.dateRange !== 'custom') {
        baseParams.preset = newFilters.dateRange;
      } else if (newFilters.from && newFilters.to) {
        baseParams.start_date = format(newFilters.from, 'yyyy-MM-dd');
        baseParams.end_date = format(newFilters.to, 'yyyy-MM-dd');
        baseParams.preset = 'custom';
      }

      return baseParams;
    };

    // Fetch with new filters
    const fetchDailyChartWithNewFilters = async () => {
      setDailyChartLoading(true);
      try {
        const params = buildApiParamsWithFilters();
        const url = buildApiUrl('daily-chart', params);
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          setDailyChartData(result.data);
        } else {
          setDailyChartData(null);
        }
      } catch (error) {
        setDailyChartData(null);
      } finally {
        setDailyChartLoading(false);
      }
    };

    const fetchKpiMetricsWithNewFilters = async () => {
      setKpiLoading(true);
      try {
        const params = buildApiParamsWithFilters();
        const url = buildApiUrl('kpi-metrics', params);
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          setKpiData(result.data);
        } else {
          setKpiData(null);
        }
      } catch (error) {
        setKpiData(null);
      } finally {
        setKpiLoading(false);
      }
    };

    const fetchTrafficChartWithNewFilters = async () => {
      setTrafficChartLoading(true);
      try {
        const params = buildApiParamsWithFilters();
        const url = buildApiUrl('traffic-chart', params);
        const response = await fetch(url);
        const result = await response.json();

        if (result.success) {
          setTrafficChartData(result.data);
        } else {
          setTrafficChartData(null);
        }
      } catch (error) {
        setTrafficChartData(null);
      } finally {
        setTrafficChartLoading(false);
      }
    };

    const fetchTrafficTableWithNewFilters = async () => {
      setTrafficTableLoading(true);
      try {
        const params = buildApiParamsWithFilters({
          limit: 10,
          page: 1,
        });
        const url = buildApiUrl('traffic-table', params);
        const response = await fetch(url);
        const result = await response.json();

        if (result.success && result.data) {
          setTrafficTableData(Array.isArray(result.data.traffic_sources) ? result.data.traffic_sources : []);
          setTrafficPagination(result.data.pagination || {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
          });
          setTrafficTotals(result.data.totals || {
            sessions: 0,
            newUsers: 0,
            totalUsers: 0,
            bounceRate: 0,
          });
          setTrafficPage(1);
        } else {
          setTrafficTableData([]);
          setTrafficPagination({
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
          });
          setTrafficTotals({
            sessions: 0,
            newUsers: 0,
            totalUsers: 0,
            bounceRate: 0,
          });
        }
      } catch (error) {
        setTrafficTableData([]);
      } finally {
        setTrafficTableLoading(false);
      }
    };

    const fetchLandingPagesWithNewFilters = async () => {
      setLandingPagesLoading(true);
      try {
        const params = buildApiParamsWithFilters({
          limit: 10,
          page: 1,
        });
        const url = buildApiUrl('landing-pages', params);
        const response = await fetch(url);
        const result = await response.json();

        if (result.success && result.data) {
          setLandingPagesData(Array.isArray(result.data.landing_pages) ? result.data.landing_pages : []);
          setLandingPagination(result.data.pagination || {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
          });
          setLandingTotals(result.data.totals || {
            sessions: 0,
            new_users: 0,
            total_users: 0,
            bounce_rate: 0,
            percentage: 0,
          });
          setLandingPage(1);
        } else {
          setLandingPagesData([]);
          setLandingPagination({
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1,
          });
          setLandingTotals({
            sessions: 0,
            new_users: 0,
            total_users: 0,
            bounce_rate: 0,
            percentage: 0,
          });
        }
      } catch (error) {
        setLandingPagesData([]);
      } finally {
        setLandingPagesLoading(false);
      }
    };

    fetchDailyChartWithNewFilters();
    fetchKpiMetricsWithNewFilters();
    fetchTrafficChartWithNewFilters();
    fetchTrafficTableWithNewFilters();
    fetchLandingPagesWithNewFilters();
  };

  useEffect(() => {
    // Trigger API calls on document load
    fetchDailyChart();
    fetchKpiMetrics();
    fetchTrafficChart();
    fetchTrafficTable();
    fetchLandingPages();
  }, []);

  const summaryData = kpiData?.summary || {
    total_users: 0,
    sessions: 0,
    page_views: 0,
    avg_session_duration: '0:0',
    bounce_rate: 0,
    formatted_users: '0',
    formatted_sessions: '0',
    formatted_page_views: '0',
    kpi_metrics: {
      sessions: {
        current: '0',
        relative_change: '0%',
        absolute_change: '0',
        is_positive: true,
      },
      total_users: {
        current: '0',
        relative_change: '0%',
        absolute_change: '0',
        is_positive: true,
      },
      new_users: {
        current: '0',
        relative_change: '0%',
        absolute_change: '0',
        is_positive: true,
      },
      returning_users: {
        current: '0',
        relative_change: '0%',
        absolute_change: '0',
        is_positive: true,
      },
      bounce_rate: {
        current: '0%',
        relative_change: '0%',
        absolute_change: '0%',
        is_positive: true,
      },
      avg_session_duration: {
        current: '00:00:00',
        relative_change: '0%',
        absolute_change: '00:00:00',
        is_positive: true,
      },
    },
  };

  const chartDataWithPrevious = useMemo(() => {
    // Ensure we have arrays to work with
    const chartData = Array.isArray(dailyChartData?.chart_data) ? dailyChartData.chart_data : [];
    const previousChartData = Array.isArray(dailyChartData?.previous_chart_data) ? dailyChartData.previous_chart_data : [];

    const generateFallbackData = () => {
      const days = filters.days || 30;
      const data = [];

      for (let i = 0; i < days; i++) {
        const date = new Date();
        date.setDate(date.getDate() - (days - 1 - i));
        data.push({
          date: date.toISOString().split('T')[0],
          users: 0,
          sessions: 0,
          page_views: 0,
        });
      }

      return data;
    };

    const fallbackData = generateFallbackData();
    const finalData = chartData.length > 0 ? chartData : fallbackData;

    return finalData.map((item, index) => {
      const previousItem = previousChartData[index] || {};

      const result = {
        ...item,
        previousSessions: previousItem.sessions || 0,
        previousDate: previousItem.date,
        sessions: item.sessions || 0,
      };

      return result;
    });
  }, [
    dailyChartData?.chart_data,
    dailyChartData?.previous_chart_data,
    filters.days,
  ]);

  const fetchTrafficPage = async page => {
    await fetchTrafficTable(page);
  };

  const fetchLandingPagesPage = async page => {
    await fetchLandingPages(page);
  };

  return (
    <div className='space-y-4'>
      <div className='flex items-center justify-between'>
        <div className='flex items-center space-x-4'>
          <h1 className='text-2xl font-semibold'>Dashboard</h1>
        </div>

        <div className='flex-shrink-0'>
          <AnalyticsFilter
            onFilterChange={handleFilterChange}
            currentFilters={filters}
          />
        </div>
      </div>
      <div className='space-y-4'>
        <DailyPerformanceChart
          data={chartDataWithPrevious}
          previousPeriodLabel={dailyChartData?.previous_period_label}
          loading={dailyChartLoading}
        />

        <div className='grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'>
          {kpiLoading ? (
            Array.from({ length: 6 }).map((_, index) => (
              <div
                key={index}
                className='flex flex-col space-y-2 p-4 bg-white rounded-lg border animate-pulse'
              >
                <div className='h-3 bg-gray-300 rounded w-16'></div>
                <div className='h-6 bg-gray-300 rounded w-12'></div>
                <div className='h-3 bg-gray-300 rounded w-20'></div>
              </div>
            ))
          ) : (
            <>
              <KPIMetric
                title='Sessions'
                currentValue={summaryData.kpi_metrics.sessions.current}
                relativeChange={
                  summaryData.kpi_metrics.sessions.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.sessions.absolute_change
                }
                isPositive={summaryData.kpi_metrics.sessions.is_positive}
              />
              <KPIMetric
                title='Total Users'
                currentValue={summaryData.kpi_metrics.total_users.current}
                relativeChange={
                  summaryData.kpi_metrics.total_users.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.total_users.absolute_change
                }
                isPositive={summaryData.kpi_metrics.total_users.is_positive}
              />
              <KPIMetric
                title='New Users'
                currentValue={summaryData.kpi_metrics.new_users.current}
                relativeChange={
                  summaryData.kpi_metrics.new_users.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.new_users.absolute_change
                }
                isPositive={summaryData.kpi_metrics.new_users.is_positive}
              />
              <KPIMetric
                title='Returning Users'
                currentValue={summaryData.kpi_metrics.returning_users.current}
                relativeChange={
                  summaryData.kpi_metrics.returning_users.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.returning_users.absolute_change
                }
                isPositive={summaryData.kpi_metrics.returning_users.is_positive}
              />
              <KPIMetric
                title='Bounce Rate'
                currentValue={summaryData.kpi_metrics.bounce_rate.current}
                relativeChange={
                  summaryData.kpi_metrics.bounce_rate.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.bounce_rate.absolute_change
                }
                isPositive={summaryData.kpi_metrics.bounce_rate.is_positive}
              />
              <KPIMetric
                title='Avg. Session Duration'
                currentValue={
                  summaryData.kpi_metrics.avg_session_duration.current
                }
                relativeChange={
                  summaryData.kpi_metrics.avg_session_duration.relative_change
                }
                absoluteChange={
                  summaryData.kpi_metrics.avg_session_duration.absolute_change
                }
                isPositive={
                  summaryData.kpi_metrics.avg_session_duration.is_positive
                }
              />
            </>
          )}
        </div>

        <div className='grid gap-6 lg:grid-cols-2'>
          <TrafficAcquisitionChart
            data={trafficChartData?.traffic_acquisition ? [
              {
                source_medium: 'New Users',
                sessions: trafficChartData.traffic_acquisition.new_sessions || 0,
                percentage: trafficChartData.traffic_acquisition.new_sessions_percentage || 0
              },
              {
                source_medium: 'Returning Users',
                sessions: trafficChartData.traffic_acquisition.returning_sessions || 0,
                percentage: trafficChartData.traffic_acquisition.returning_sessions_percentage || 0
              }
            ] : []}
            loading={trafficChartLoading}
          />
          <TopTrafficAcquisitionTable
            data={trafficTableData}
            pagination={trafficPagination}
            page={trafficPage}
            onPageChange={fetchTrafficPage}
            loading={trafficTableLoading}
            totals={trafficTotals}
            summaryData={summaryData}
          />
        </div>
        {/* top 10 Landing Page */}
        <div className='grid gap-6 lg:grid-cols-1'>
          <TopLandingPagesTable
            data={landingPagesData}
            pagination={landingPagination}
            page={landingPage}
            onPageChange={fetchLandingPagesPage}
            loading={landingPagesLoading}
            totals={landingTotals}
            summaryData={summaryData}
          />
        </div>
      </div>
    </div>
  );
};

export default GoogleAnalytics;