import { TrendingUp, TrendingDown } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/Components/ui/table';

const TopLandingPagesTable = ({
  data = [],
  pagination = { current_page: 1, per_page: 10, total: 0, last_page: 1 },
  page = 1,
  onPageChange,
  loading = false,
  totals,
  summaryData,
}) => {
  const from =
    pagination && pagination.total > 0
      ? (page - 1) * pagination.per_page + 1
      : 0;
  const to = pagination && pagination.total > 0 ? from + data.length - 1 : 0;
  const total = pagination ? pagination.total : data.length;
  const lastPage = pagination ? pagination.last_page : 1;

  const truncateUrl = (url, maxLength = 50) => {
    if (!url || url.length <= maxLength) return url;
    return url.substring(0, maxLength) + '...';
  };

  if (loading) {
    return (
      <Card>
        <CardHeader>
          <div className='flex items-center justify-between'>
            <CardTitle className='text-lg'>Top Landing Pages</CardTitle>
            <div className='flex items-center space-x-2'>
              <TrendingUp className='h-4 w-4 cursor-pointer' />
              <TrendingDown className='h-4 w-4 cursor-pointer' />
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div className='flex items-center justify-center h-32'>
            <span className='animate-spin rounded-full h-8 w-8 border-b-2 border-primary'></span>
            <span className='ml-2'>Loading...</span>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card>
      <CardHeader>
        <div className='flex items-center justify-between'>
          <CardTitle className='text-lg'>Top Landing Pages</CardTitle>
          <div className='flex items-center space-x-2'>
            <TrendingUp className='h-4 w-4 cursor-pointer' />
            <TrendingDown className='h-4 w-4 cursor-pointer' />
          </div>
        </div>
      </CardHeader>
      <CardContent>
        {data.length === 0 ? (
          <div className='flex items-center justify-center h-32'>
            <p>No landing pages data available</p>
          </div>
        ) : (
          <>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Landing Page</TableHead>
                  <TableHead className='text-right'>Sessions</TableHead>
                  <TableHead className='text-right'>New users</TableHead>
                  <TableHead className='text-right'>Total users</TableHead>
                  <TableHead className='text-right'>Bounce rate</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.map((item, index) => (
                  <TableRow key={`${item.landing_page || item.page_path}-${index}`}>
                    <TableCell className='font-medium px-2 py-2'>
                      <div className='flex flex-col'>
                        <span className='text-sm' title={item.page_title || item.page_path || item.landing_page}>
                          {item.page_title || truncateUrl(item.page_path || item.landing_page)}
                        </span>
                        <span className='text-xs text-gray-500' title={item.page_path || item.landing_page}>
                          {truncateUrl(item.page_path || item.landing_page)}
                        </span>
                      </div>
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {item.formatted_sessions || (item.sessions || 0).toLocaleString()}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {item.formatted_new_users || (item.new_users || 0).toLocaleString()}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {item.formatted_total_users || (item.total_users || item.users || 0).toLocaleString()}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {item.bounce_rate ? `${typeof item.bounce_rate === 'string' ? item.bounce_rate : (item.bounce_rate * 100).toFixed(1)}%` : '0%'}
                    </TableCell>
                  </TableRow>
                ))}
                {summaryData && (
                  <TableRow className='bg-gray-50'>
                    <TableCell className='font-semibold'>
                      Total overall
                    </TableCell>
                    <TableCell className='text-right font-semibold'>
                      {summaryData.kpi_metrics.sessions.current}
                    </TableCell>
                    <TableCell className='text-right font-semibold'>
                      {summaryData.kpi_metrics.new_users.current}
                    </TableCell>
                    <TableCell className='text-right font-semibold'>
                      {summaryData.kpi_metrics.total_users.current}
                    </TableCell>
                    <TableCell className='text-right font-semibold'>
                      {summaryData.kpi_metrics.bounce_rate.current}%
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
            <div className='flex justify-between items-center mt-4 text-xs text-gray-600'>
              <span>
                {from} - {to} / {total}
              </span>
              <div className='flex items-center space-x-2'>
                <button
                  className='px-2 py-1 border rounded disabled:opacity-50'
                  onClick={() => onPageChange && onPageChange(page - 1)}
                  disabled={page <= 1}
                >
                  Prev
                </button>
                <span>
                  Page {page} of {lastPage}
                </span>
                <button
                  className='px-2 py-1 border rounded disabled:opacity-50'
                  onClick={() => onPageChange && onPageChange(page + 1)}
                  disabled={page >= lastPage}
                >
                  Next
                </button>
              </div>
            </div>
          </>
        )}
      </CardContent>
    </Card>
  );
};

export default TopLandingPagesTable;
