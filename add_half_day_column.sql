-- Add half-day functionality to working_calendar table
-- This makes more sense as half-day is typically an organization-wide decision

ALTER TABLE working_calendar 
ADD COLUMN is_half_day TINYINT(1) DEFAULT 0 
COMMENT 'Indicates if this is a half-day for the organization (0=full day, 1=half day)';

-- Optional: You can also add an index for better query performance
CREATE INDEX idx_working_calendar_half_day ON working_calendar(is_half_day);

-- Verify the column was added successfully
DESCRIBE working_calendar;
