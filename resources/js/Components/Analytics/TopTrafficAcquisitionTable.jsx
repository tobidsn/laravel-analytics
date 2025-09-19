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

const TopTrafficAcquisitionTable = ({
  data,
  pagination,
  page,
  onPageChange,
  loading,
  totals,
  summaryData,
}) => {
  const overallTotals = totals || {
    sessions: 0,
    newUsers: 0,
    totalUsers: 0,
    bounceRate: 0,
  };
  const from =
    pagination && pagination.total > 0
      ? (page - 1) * pagination.per_page + 1
      : 0;
  const to = pagination && pagination.total > 0 ? from + data.length - 1 : 0;
  const total = pagination ? pagination.total : data.length;
  const lastPage = pagination ? pagination.last_page : 1;

  return (
    <Card>
      <CardHeader>
        <div className='flex items-center justify-between'>
          <CardTitle className='text-lg'>Top Traffic Acquisition</CardTitle>
          <div className='flex items-center space-x-2'>
            <TrendingUp className='h-4 w-4  cursor-pointer' />
            <TrendingDown className='h-4 w-4  cursor-pointer' />
          </div>
        </div>
      </CardHeader>
      <CardContent>
        {loading ? (
          <div className='flex items-center justify-center h-32 '>
            <span className='animate-spin rounded-full h-8 w-8 border-b-2 border-primary'></span>
            <span className='ml-2'>Loading...</span>
          </div>
        ) : data.length === 0 ? (
          <div className='flex items-center justify-center h-32 '>
            <p>No traffic data available</p>
          </div>
        ) : (
          <>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Session source / medium</TableHead>
                  <TableHead className='text-right'>Sessions</TableHead>
                  <TableHead className='text-right'>New users</TableHead>
                  <TableHead className='text-right'>Total users</TableHead>
                  <TableHead className='text-right'>Bounce rate</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {data.map((source, index) => (
                  <TableRow key={index}>
                    <TableCell className='font-medium px-2 py-2'>
                      {source.source}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {source.sessions}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {source.newUsers}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {source.totalUsers}
                    </TableCell>
                    <TableCell className='text-right px-2 py-2'>
                      {source.bounceRate}%
                    </TableCell>
                  </TableRow>
                ))}
                {summaryData && (
                  <TableRow className='bg-gray-50'>
                    <TableCell className='font-semibold'>Total overall</TableCell>
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
                  onClick={() => onPageChange(page - 1)}
                  disabled={page <= 1}
                >
                  Prev
                </button>
                <span>
                  Page {page} of {lastPage}
                </span>
                <button
                  className='px-2 py-1 border rounded disabled:opacity-50'
                  onClick={() => onPageChange(page + 1)}
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

export default TopTrafficAcquisitionTable;
