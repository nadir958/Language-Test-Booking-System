export type User = {
  id: string;
  name: string;
  email: string;
  roles?: string[];
};

export type SessionItem = {
  id: string;
  language: string;
  location: string;
  startAt: string;
  seats: number;
  seatsRemaining?: number;
};

export type Reservation = {
  id: string;
  session: SessionItem;
  createdAt: string;
};

export type PaginatedSessions = {
  data: SessionItem[];
  pagination: {
    page: number;
    limit: number;
    total: number;
  };
};
