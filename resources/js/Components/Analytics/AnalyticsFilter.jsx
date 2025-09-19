import { useState } from 'react';
import { Calendar, ChevronDown } from 'lucide-react';
import { Button } from '@/Components/ui/button';
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/Components/ui/popover';
import { Calendar as CalendarComponent } from '@/Components/ui/calendar';
import {
  format,
  subDays,
  startOfYear,
  endOfYear,
  startOfMonth,
  endOfMonth,
  subMonths,
} from 'date-fns';
import { cn } from '@/lib/utils';

const dateRanges = [
  { label: 'Today', value: 'today', days: 1 },
  { label: 'Yesterday', value: 'yesterday', days: 1 },
  { label: 'Last 7 days', value: '7d', days: 7 },
  { label: 'Last 30 days', value: '30d', days: 30 },
  { label: 'Last Month', value: 'last_month', days: 30 },
  { label: 'Last 90 days', value: '90d', days: 90 },
  { label: 'This year', value: 'ytd', days: 365 },
];

export default function AnalyticsFilter({
  onFilterChange,
  currentFilters = {},
}) {
  const [dateRange, setDateRange] = useState(currentFilters.dateRange || '7d');
  const [customDateRange, setCustomDateRange] = useState({
    from: currentFilters.from || new Date(),
    to: currentFilters.to || new Date(),
  });
  const [isOpen, setIsOpen] = useState(false);
  const [pendingDateRange, setPendingDateRange] = useState(
    currentFilters.dateRange || '7d'
  );
  const [pendingCustomDateRange, setPendingCustomDateRange] = useState(() => {
    if (currentFilters.dateRange === 'last_month') {
      return {
        from: startOfMonth(subMonths(new Date(), 1)),
        to: endOfMonth(subMonths(new Date(), 1)),
      };
    }
    return {
      from: currentFilters.from || new Date(),
      to: currentFilters.to || new Date(),
    };
  });
  const [captionLayout, setCaptionLayout] = useState('dropdown');

  const handleDateRangeChange = value => {
    setPendingDateRange(value);
    let from, to;

    switch (value) {
      case 'today':
        from = new Date();
        to = new Date();
        break;
      case 'yesterday':
        from = subDays(new Date(), 1);
        to = subDays(new Date(), 1);
        break;
      case '7d':
        from = subDays(new Date(), 6);
        to = new Date();
        break;
      case '30d':
        from = subDays(new Date(), 29);
        to = new Date();
        break;
      case 'last_month':
        from = startOfMonth(subMonths(new Date(), 1));
        to = endOfMonth(subMonths(new Date(), 1));
        break;
      case '90d':
        from = subDays(new Date(), 89);
        to = new Date();
        break;
      case 'ytd':
        from = startOfYear(new Date());
        to = endOfYear(new Date());
        break;
      default:
        from = pendingCustomDateRange.from;
        to = pendingCustomDateRange.to;
    }

    setPendingCustomDateRange({ from, to });
  };

  const handleCustomDateChange = (date, type) => {
    const newCustomRange = {
      ...pendingCustomDateRange,
      [type]: date,
    };
    setPendingCustomDateRange(newCustomRange);
    setPendingDateRange('custom');
  };

  const handleApply = () => {
    const daysDiff = Math.ceil(
      (pendingCustomDateRange.to - pendingCustomDateRange.from) /
        (1000 * 60 * 60 * 24)
    );

    onFilterChange({
      dateRange: pendingDateRange,
      from: pendingCustomDateRange.from,
      to: pendingCustomDateRange.to,
      days: daysDiff + 1,
    });

    setDateRange(pendingDateRange);
    setCustomDateRange(pendingCustomDateRange);
    setIsOpen(false);
  };

  const handleCancel = () => {
    setPendingDateRange(dateRange);
    setPendingCustomDateRange(customDateRange);
    setIsOpen(false);
  };

  const getDisplayText = () => {
    if (isOpen) {
      if (
        pendingDateRange === 'custom' &&
        pendingCustomDateRange.from &&
        pendingCustomDateRange.to
      ) {
        return `${format(
          pendingCustomDateRange.from,
          'dd MMM yyyy'
        )} - ${format(pendingCustomDateRange.to, 'dd MMM yyyy')}`;
      }

      const selectedRange = dateRanges.find(
        range => range.value === pendingDateRange
      );
      if (selectedRange) {
        let from, to;
        switch (selectedRange.value) {
          case 'today':
            from = new Date();
            to = new Date();
            break;
          case 'yesterday':
            from = subDays(new Date(), 1);
            to = subDays(new Date(), 1);
            break;
          case '7d':
            from = subDays(new Date(), 6);
            to = new Date();
            break;
          case '30d':
            from = subDays(new Date(), 29);
            to = new Date();
            break;
          case 'last_month':
            from = startOfMonth(subMonths(new Date(), 1));
            to = endOfMonth(subMonths(new Date(), 1));
            break;
          case '90d':
            from = subDays(new Date(), 89);
            to = new Date();
            break;
          case 'ytd':
            from = startOfYear(new Date());
            to = endOfYear(new Date());
            break;
          default:
            from = new Date();
            to = new Date();
        }
        return `${format(from, 'dd MMM yyyy')} - ${format(to, 'dd MMM yyyy')}`;
      }
    }

    if (dateRange === 'custom' && customDateRange.from && customDateRange.to) {
      return `${format(customDateRange.from, 'dd MMM yyyy')} - ${format(
        customDateRange.to,
        'dd MMM yyyy'
      )}`;
    }

    const selectedRange = dateRanges.find(range => range.value === dateRange);
    if (selectedRange) {
      let from, to;
      switch (selectedRange.value) {
        case 'today':
          from = new Date();
          to = new Date();
          break;
        case 'yesterday':
          from = subDays(new Date(), 1);
          to = subDays(new Date(), 1);
          break;
        case '7d':
          from = subDays(new Date(), 6);
          to = new Date();
          break;
        case '30d':
          from = subDays(new Date(), 29);
          to = new Date();
          break;
        case 'last_month':
          from = startOfMonth(subMonths(new Date(), 1));
          to = endOfMonth(subMonths(new Date(), 1));
          break;
        case '90d':
          from = subDays(new Date(), 89);
          to = new Date();
          break;
        case 'ytd':
          from = startOfYear(new Date());
          to = endOfYear(new Date());
          break;
        default:
          from = new Date();
          to = new Date();
      }
      return `${format(from, 'dd MMM yyyy')} - ${format(to, 'dd MMM yyyy')}`;
    }

    return 'Select date range';
  };

  const getCurrentFromTo = () => {
    if (pendingDateRange === 'custom') {
      return {
        from: pendingCustomDateRange.from,
        to: pendingCustomDateRange.to,
      };
    }

    let from, to;
    switch (pendingDateRange) {
      case 'today':
        from = new Date();
        to = new Date();
        break;
      case 'yesterday':
        from = subDays(new Date(), 1);
        to = subDays(new Date(), 1);
        break;
      case '7d':
        from = subDays(new Date(), 6);
        to = new Date();
        break;
      case '30d':
        from = subDays(new Date(), 29);
        to = new Date();
        break;
      case 'last_month':
        from = startOfMonth(subMonths(new Date(), 1));
        to = endOfMonth(subMonths(new Date(), 1));
        break;
      case '90d':
        from = subDays(new Date(), 89);
        to = new Date();
        break;
      case 'ytd':
        from = startOfYear(new Date());
        to = endOfYear(new Date());
        break;
      default:
        from = new Date();
        to = new Date();
    }
    return { from, to };
  };

  const { from, to } = getCurrentFromTo();

  return (
    <Popover open={isOpen} onOpenChange={setIsOpen}>
      <PopoverTrigger asChild>
        <Button
          variant='outline'
          className={cn(
            'w-[280px] justify-start text-left font-normal bg-white  hover:bg-gray-50',
            !from && 'text-muted-foreground'
          )}
        >
          <Calendar className='mr-2 h-4 w-4' />
          {getDisplayText()}
          <ChevronDown className='ml-auto h-4 w-4' />
        </Button>
      </PopoverTrigger>
      <PopoverContent className='w-auto p-0' align='end'>
        <div className='flex'>
          <div className='p-3 border-r'>
            <div className='space-y-4'>
              <div className='space-y-2'>
                <CalendarComponent
                  mode='range'
                  defaultMonth={
                    pendingDateRange === 'last_month'
                      ? subMonths(new Date(), 1)
                      : from
                  }
                  selected={pendingCustomDateRange}
                  onSelect={range => {
                    if (range?.from && range?.to) {
                      setPendingCustomDateRange(range);
                      setPendingDateRange('custom');
                    }
                  }}
                  numberOfMonths={2}
                  captionLayout={captionLayout}
                  className='rounded-lg border shadow-sm'
                />
              </div>
            </div>
          </div>

          <div className='p-3 w-48'>
            <div className='space-y-2'>
              {dateRanges.map(range => (
                <button
                  key={range.value}
                  type='button'
                  className={cn(
                    'w-full text-left px-3 py-2 text-sm rounded-md transition-all duration-200 font-medium',
                    pendingDateRange === range.value
                      ? 'bg-blue-600 text-white shadow-sm'
                      : ' hover:bg-gray-100 hover:text-gray-900'
                  )}
                  onClick={() => handleDateRangeChange(range.value)}
                >
                  {range.label}
                </button>
              ))}
              <button
                type='button'
                className={cn(
                  'w-full text-left px-3 py-2 text-sm rounded-md transition-all duration-200 font-medium',
                  pendingDateRange === 'custom'
                    ? 'bg-blue-600 text-white shadow-sm'
                    : ' hover:bg-gray-100 hover:text-gray-900'
                )}
                onClick={() => setPendingDateRange('custom')}
              >
                Custom Range
              </button>
            </div>

            {/* Action Buttons */}
            <div className='flex gap-2 mt-4 pt-4 border-t'>
              <Button
                variant='outline'
                size='sm'
                onClick={handleCancel}
                className='flex-1'
              >
                Cancel
              </Button>
              <Button size='sm' onClick={handleApply} className='flex-1'>
                Apply
              </Button>
            </div>
          </div>
        </div>
      </PopoverContent>
    </Popover>
  );
}