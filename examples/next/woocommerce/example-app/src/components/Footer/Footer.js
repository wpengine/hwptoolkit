import { useQuery, gql } from "@apollo/client";

const SETTINGS_QUERY = gql`
  query FooterSettingsQuery {
    generalSettings {
      title
    }
  }
`;

export default function Footer({ footerData }) {
  const settingsData = footerData?.settings;

  //CSR maybe?
  // const {
  //   data: footerData,
  //   loading,
  //   error,
  // } = useQuery(SETTINGS_QUERY, {
  //   fetchPolicy: "cache-first",
  //   },
  // });

  return (
    <footer className="footer">
      <div className="footer-content">
        <p>
          &copy; {new Date().getFullYear()}{" "}
          {settingsData?.generalSettings?.title || "My Site"}. All rights
          reserved.
        </p>
      </div>

      <style jsx>{`
        .footer {
          background: #f8f9fa;
          border-top: 1px solid #e1e5e9;
          padding: 1rem 0;
          margin-top: auto;
        }

        .footer-content {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 1rem;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .footer-content p {
          margin: 0;
          color: #6c757d;
          font-size: 14px;
        }

        @media (max-width: 768px) {
          .footer-content p {
            font-size: 12px;
            text-align: center;
          }
        }
      `}</style>
    </footer>
  );
}
