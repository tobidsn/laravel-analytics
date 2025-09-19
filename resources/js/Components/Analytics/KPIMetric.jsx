import { TrendingUp, TrendingDown } from 'lucide-react';

const KPIMetric = ({
  title,
  currentValue,
  relativeChange,
  absoluteChange,
  isPositive,
}) => {
  return (
    <div className='flex flex-col space-y-2 p-4 bg-white rounded-lg border'>
      <div className='text-xs font-medium text-gray-600'>{title}</div>
      <div className='text-xl font-semibold'>{currentValue}</div>
      <div className='flex items-center space-x-2'>
        <div
          className={`flex items-center text-xs ${isPositive ? 'text-green-600' : 'text-red-600'}`}
        >
          {isPositive ? (
            <TrendingUp className='h-3 w-3 mr-1' />
          ) : (
            <TrendingDown className='h-3 w-3 mr-1' />
          )}
          {relativeChange}
        </div>
        <div
          className={`text-xs ${isPositive ? 'text-green-600' : 'text-red-600'}`}
        >
          {absoluteChange}
        </div>
      </div>
    </div>
  );
};

export default KPIMetric;