import { fireEvent, render, screen } from '@testing-library/react';
import { AuthPanel } from '../components/AuthPanel';

const mockLogin = jest.fn();
const mockRegister = jest.fn();

jest.mock('../context/AuthContext', () => ({
  useAuth: () => ({
    login: mockLogin,
    register: mockRegister,
  }),
}));

describe('AuthPanel', () => {
  it('renders login form by default and toggles to register', () => {
    render(<AuthPanel />);

    expect(screen.getByText(/login/i)).toHaveClass('active');
    expect(screen.getByLabelText(/email/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/password/i)).toBeInTheDocument();
    expect(screen.queryByLabelText(/name/i)).not.toBeInTheDocument();

    fireEvent.click(screen.getByText(/register/i));

    expect(screen.getByText(/register/i)).toHaveClass('active');
    expect(screen.getByLabelText(/name/i)).toBeInTheDocument();
  });
});
