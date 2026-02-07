# Contributing to HyperMon

Thanks for your interest in contributing to HyperMon. This project aims to provide a simple, mobile-first interface for AllStarLink, and we need help from the community to make it happen.

## Project Goals

Keep these in mind when contributing:

1. **Mobile-first** - Every feature should work well on a phone
2. **Simple** - Don't add complexity unless it solves a real problem
3. **Fast** - Minimize resource usage on Raspberry Pi hardware
4. **Accessible** - Easy to install and use for typical AllStarLink operators

## What We Need Help With

### High Priority

- AMI (Asterisk Manager Interface) integration code
- Fetching and parsing AllStarLink keyed nodes API
- Mobile-responsive CSS layout
- Touch-friendly UI components
- Testing on actual AllStarLink nodes

### Medium Priority

- Favorites management (localStorage)
- Recent connections tracking
- Node search/filter functionality
- Installation and update scripts

### Documentation

- Installation instructions
- Configuration guide
- Troubleshooting common issues
- API documentation

## Code Style

**PHP:**
- Keep it simple and readable
- Comment non-obvious code
- Follow existing AllStarLink tools conventions (see Supermon/AllScan)

**JavaScript:**
- Vanilla JS (no frameworks for now)
- Use modern ES6+ features
- Keep functions small and focused

**CSS:**
- Mobile-first approach
- Use CSS variables for theming
- Comment complex layouts

## Pull Request Process

1. Fork the repository
2. Create a branch for your feature (`git checkout -b feature/your-feature`)
3. Make your changes
4. Test on mobile and desktop if possible
5. Update documentation if needed
6. Submit a pull request with a clear description

## Testing

If you can test on real hardware:
- Raspberry Pi 3/4/5
- Different mobile devices (iOS/Android)
- Different browsers (Chrome, Safari, Firefox)

Document your testing in the PR.

## Questions?

Use the GitHub Discussions tab for questions about:
- How to contribute
- Design decisions
- Feature ideas
- Technical implementation

Use Issues for:
- Bug reports
- Specific feature requests
- Tasks that need doing

## Code of Conduct

Be respectful and constructive. We're all here to make AllStarLink better.
