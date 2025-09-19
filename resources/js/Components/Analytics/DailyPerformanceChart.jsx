import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/Components/ui/card';
import {
  LineChart,
  Line,
  XAxis,
  YAxis,
  CartesianGrid,
  Legend,
  ResponsiveContainer,
  Tooltip,
} from 'recharts';

const DailyPerformanceChart = ({
  data,
  previousPeriodLabel = 'Previous period',
  loading = false,
}) => {
  const getYAxisDomain = () => {
    if (!data || data.length === 0) return [0, 100];

    const allValues = data
      .flatMap(item => [item.sessions || 0, item.previousSessions || 0])
      .filter(value => value !== null && value !== undefined);

    if (allValues.length === 0) return [0, 100];

    const maxValue = Math.max(...allValues);
    const minValue = Math.min(...allValues);

    const padding = maxValue * 0.1;
    return [Math.max(0, minValue - padding), maxValue + padding];
  };

  const yAxisDomain = getYAxisDomain();

  const CustomTooltip = ({ active, payload, label }) => {
    if (active && payload && payload.length) {
      const currentDate = new Date(label);
      const formattedCurrentDate = currentDate.toLocaleDateString('en-US', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
      });

      return (
        <div className='bg-white border rounded-lg shadow-lg p-3'>
          <div className='space-y-2'>
            {payload.map((entry, index) => {
              let displayDate = formattedCurrentDate;
              if (entry.dataKey === 'previousSessions') {
                if (entry.payload?.previousDate) {
                  const previousDate = new Date(entry.payload.previousDate);
                  displayDate = previousDate.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                  });
                } else {
                  const currentDate = new Date(label);
                  const previousDate = new Date(currentDate);
                  previousDate.setDate(previousDate.getDate() - 1);
                  displayDate = previousDate.toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                  });
                }
              }

              return (
                <div key={index}>
                  <div className='text-sm font-medium text-gray-900 mb-1'>
                    {displayDate}
                  </div>
                  <div className='flex items-center space-x-2'>
                    <span className='text-sm text-gray-600'>●</span>
                    <span className='text-sm text-gray-600'>{entry.name}:</span>
                    <span className='text-sm font-medium text-gray-900'>
                      {entry.value}
                    </span>
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      );
    }
    return null;
  };

  if (loading) {
    return (
      <Card className='col-span-full'>
        <CardHeader>
          <div className='flex items-center justify-between'>
            <div>
              <CardTitle className='text-lg'>Daily Performance</CardTitle>
              <CardDescription className='text-xs'>
                Daily trends for the selected period
              </CardDescription>
            </div>
            <div className='flex items-center space-x-2'>
              <div className='flex items-center space-x-1 text-xs'>
                <div className='w-3 h-3 bg-yellow-400 rounded-full'></div>
                <span>Sessions</span>
              </div>
              <div className='flex items-center space-x-1 text-xs'>
                <div className='w-3 h-3 bg-gray-300 rounded-full'></div>
                <span>
                  Sessions ({previousPeriodLabel || 'Previous period'})
                </span>
              </div>
            </div>
          </div>
        </CardHeader>
        <CardContent className='pr-8 pl-1'>
          <div className='h-[300px] flex items-center justify-center'>
            <div className='animate-spin rounded-full h-8 w-8 border-b-2 border-primary'></div>
            <span className='ml-2'>Loading chart data...</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className='col-span-full'>
      <CardHeader>
        <div className='flex items-center justify-between'>
          <div>
            <CardTitle className='text-lg'>Daily Performance</CardTitle>
            <CardDescription className='text-xs'>
              Daily trends for the selected period
            </CardDescription>
          </div>
          <div className='flex items-center space-x-2'>
            <div className='flex items-center space-x-1 text-xs'>
              <div className='w-3 h-3 bg-yellow-400 rounded-full'></div>
              <span>Sessions</span>
            </div>
            <div className='flex items-center space-x-1 text-xs'>
              <div className='w-3 h-3 bg-gray-300 rounded-full'></div>
              <span>Sessions ({previousPeriodLabel || 'Previous period'})</span>
            </div>
          </div>
        </div>
      </CardHeader>
      <CardContent className='pr-8 pl-1'>
        <div className='h-[300px]'>
          <ResponsiveContainer width='100%' height='100%'>
            <LineChart data={data}>
              <CartesianGrid strokeDasharray='3 3' stroke='#f0f0f0' />
              <XAxis
                dataKey='date'
                tick={{ fontSize: 10 }}
                tickFormatter={value =>
                  new Date(value).toLocaleDateString('en-US', {
                    day: 'numeric',
                    month: 'short',
                  })
                }
              />
              <YAxis tick={{ fontSize: 10 }} domain={yAxisDomain} />
              <Tooltip content={<CustomTooltip />} />
              <Legend />
              <Line
                type='monotone'
                dataKey='sessions'
                stroke='#f59e0b'
                strokeWidth={2}
                dot={{ fill: '#f59e0b', strokeWidth: 2, r: 4 }}
                activeDot={{ r: 6, stroke: '#f59e0b', strokeWidth: 2 }}
                name='Sessions'
              />
              <Line
                type='monotone'
                dataKey='previousSessions'
                stroke='#d1d5db'
                strokeWidth={2}
                dot={{ fill: '#d1d5db', strokeWidth: 2, r: 4 }}
                activeDot={{ r: 6, stroke: '#d1d5db', strokeWidth: 2 }}
                name={`Sessions (${previousPeriodLabel || 'Previous period'})`}
              />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </CardContent>
    </Card>
  );
};

export default DailyPerformanceChart;