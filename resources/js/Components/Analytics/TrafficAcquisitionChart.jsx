import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip } from 'recharts';

const TrafficAcquisitionChart = ({ data = [], loading = false }) => {
  const colors = [
    '#2563eb', '#dc2626', '#059669', '#d97706', '#7c3aed',
    '#db2777', '#0891b2', '#65a30d', '#c2410c', '#9333ea'
  ];

  const chartData = data.map((item, index) => ({
    name: item.source_medium || item.source || 'Unknown',
    value: item.sessions || 0,
    percentage: item.percentage || 0,
    color: colors[index % colors.length]
  }));

  const total = chartData.reduce((sum, item) => sum + item.value, 0);

  const CustomTooltip = ({ active, payload }) => {
    if (active && payload && payload.length) {
      const data = payload[0].payload;
      return (
        <div className='bg-white border rounded-lg shadow-lg p-3'>
          <div className='text-sm font-medium text-gray-900 mb-1'>
            {data.name}
          </div>
          <div className='text-sm text-gray-600'>
            Sessions: <span className='font-medium'>{data.value.toLocaleString()}</span>
          </div>
          <div className='text-sm text-gray-600'>
            Percentage: <span className='font-medium'>{data.percentage}%</span>
          </div>
        </div>
      );
    }
    return null;
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className='text-lg'>Traffic Sources</CardTitle>
        </CardHeader>
        <CardContent>
          <div className='h-[300px] flex items-center justify-center'>
            <div className='animate-spin rounded-full h-8 w-8 border-b-2 border-primary'></div>
            <span className='ml-2'>Loading chart data...</span>
          </div>
          <div className='flex justify-center space-x-4 mt-4'>
            {colors.slice(0, 3).map((color, index) => (
              <div key={index} className='flex items-center space-x-2'>
                <div className='w-3 h-3 rounded-full' style={{ backgroundColor: color }}></div>
                <span className='text-xs'>Source {index + 1}</span>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!data || data.length === 0) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className='text-lg'>Traffic Sources</CardTitle>
        </CardHeader>
        <CardContent>
          <div className='h-[300px] flex items-center justify-center text-gray-500'>
            No data available
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className='text-lg'>Traffic Sources</CardTitle>
      </CardHeader>
      <CardContent>
        <div className='h-[300px]'>
          <ResponsiveContainer width='100%' height='100%'>
            <PieChart>
              <Pie
                data={chartData}
                cx='50%'
                cy='50%'
                innerRadius={60}
                outerRadius={120}
                paddingAngle={2}
                dataKey='value'
              >
                {chartData.map((entry, index) => (
                  <Cell key={`cell-${index}`} fill={entry.color} />
                ))}
              </Pie>
              <Tooltip content={<CustomTooltip />} />
            </PieChart>
          </ResponsiveContainer>
        </div>

        {/* Legend */}
        <div className='mt-4 space-y-2'>
          {chartData.slice(0, 5).map((item, index) => (
            <div key={index} className='flex items-center justify-between py-1'>
              <div className='flex items-center space-x-3'>
                <div
                  className='w-3 h-3 rounded-full'
                  style={{ backgroundColor: item.color }}
                ></div>
                <span className='text-sm font-medium text-gray-900 capitalize'>
                  {item.name}
                </span>
              </div>
              <div className='text-right'>
                <div className='text-sm font-semibold text-gray-900'>
                  {item.value.toLocaleString()}
                </div>
                <div className='text-xs text-gray-500'>
                  {item.percentage}%
                </div>
              </div>
            </div>
          ))}
          {chartData.length > 5 && (
            <div className='text-xs text-gray-500 text-center pt-2'>
              +{chartData.length - 5} more sources
            </div>
          )}
        </div>

        {/* Summary */}
        <div className='mt-4 pt-4 border-t border-gray-200'>
          <div className='grid grid-cols-2 gap-4 text-sm'>
            <div>
              <span className='text-gray-600'>Total Sources:</span>
              <span className='ml-2 font-medium'>{data.length}</span>
            </div>
            <div>
              <span className='text-gray-600'>Total Sessions:</span>
              <span className='ml-2 font-medium'>{total.toLocaleString()}</span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  );
};

export default TrafficAcquisitionChart;